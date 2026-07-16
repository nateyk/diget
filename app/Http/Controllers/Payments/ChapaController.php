<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Services\PaymentSettlementService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ChapaController extends Controller
{
    private const CURRENCY = 'ETB';

    private const API_URL = 'https://api.chapa.co/v1';

    private $paymentGateway;

    private $client;

    public function __construct(?ClientInterface $client = null, ?PaymentGateway $paymentGateway = null)
    {
        $this->client = $client ?: new Client([
            'timeout' => 15,
            'connect_timeout' => 5,
            'verify' => config('services.chapa.ssl_verify', true),
        ]);
        $this->paymentGateway = $paymentGateway?->exists ? $paymentGateway : paymentGateway('chapa');
    }

    public function process($trx)
    {
        try {
            $this->ensureGatewayIsConfigured();

            $txRef = 'chapa_'.$trx->id.'_'.Str::random(20);
            $amount = $this->expectedAmount($trx);
            $user = $trx->user;

            $payload = [
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => self::CURRENCY,
                'email' => $user->email,
                'first_name' => $user->firstname ?: $user->username,
                'last_name' => $user->lastname ?: '',
                'tx_ref' => $txRef,
                'callback_url' => route('payments.ipn.chapa', ['tx_ref' => $txRef]),
                'return_url' => route('payments.ipn.chapa', ['tx_ref' => $txRef]),
                'customization' => [
                    'title' => @settings('general')->site_name ?: config('app.name'),
                    'description' => translate('Payment for order :number', ['number' => $trx->id]),
                ],
            ];

            $result = $this->request('POST', '/transaction/initialize', ['json' => $payload]);
            $checkoutUrl = data_get($result, 'data.checkout_url');

            if (($result['status'] ?? null) !== 'success' || ! $checkoutUrl) {
                throw new RuntimeException(translate('Unable to initialize Chapa payment'));
            }

            $trx->payment_id = $txRef;
            $trx->update();

            return json_encode([
                'type' => 'success',
                'method' => 'redirect',
                'redirect_url' => $checkoutUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chapa payment initialization failed', [
                'transaction_id' => $trx->id,
                'exception' => $e,
            ]);

            return json_encode([
                'type' => 'error',
                'msg' => translate('Unable to initialize Chapa payment. Please try again.'),
            ]);
        }
    }

    public function ipn(Request $request)
    {
        $txRef = $request->string('tx_ref')->toString()
            ?: $request->string('trx_ref')->toString();
        $trx = $this->findCustomerTransaction($txRef);

        if (! $trx) {
            Log::warning('Chapa callback payment reference not found', [
                'tx_ref' => $txRef,
                'query' => $request->query(),
                'is_authenticated' => auth()->check(),
                'user_id' => auth()->id(),
            ]);
            toastr()->error(translate('Invalid payment reference'));

            return redirect()->route('home');
        }

        $checkoutLink = route('checkout.index', hash_encode($trx->id));

        if ($trx->isPaid() && $trx->fulfilled_at) {
            $trx->user->emptyCart();

            return redirect($checkoutLink);
        }

        try {
            $verified = $this->verifyTransaction($trx->payment_id);
            if (! $this->verifiedPaymentMatches($trx, $verified)) {
                Log::warning('Chapa callback verification mismatch', ['transaction_id' => $trx->id]);
                toastr()->error(translate('Payment could not be verified'));

                return redirect($checkoutLink);
            }

            $paid = $this->markPaid($trx->id, $verified);
            if ($paid) {
                $paid->user->emptyCart();
            }

            toastr()->success(translate('Payment successful'));
        } catch (\Throwable $e) {
            Log::error('Chapa callback verification failed', [
                'transaction_id' => $trx->id,
                'exception' => $e,
            ]);
            toastr()->error(translate('Payment could not be verified'));
        }

        return redirect($checkoutLink);
    }

    public function webhook(Request $request)
    {
        if (! $this->hasValidWebhookSignature($request)) {
            return response('Invalid signature', 401);
        }

        $txRef = (string) (data_get($request->all(), 'tx_ref') ?: data_get($request->all(), 'data.tx_ref'));
        if ($txRef === '') {
            return response('Invalid payload', 400);
        }

        $trx = Transaction::where('payment_gateway_id', $this->paymentGateway->id)
            ->where('payment_id', $txRef)
            ->first();

        if (! $trx || ($trx->isPaid() && $trx->fulfilled_at)) {
            return response('Webhook acknowledged', 200);
        }

        try {
            $verified = $this->verifyTransaction($trx->payment_id);
            if (! $this->verifiedPaymentMatches($trx, $verified)) {
                Log::warning('Chapa webhook verification mismatch', ['transaction_id' => $trx->id]);

                return response('Payment not verified', 200);
            }

            $this->markPaid($trx->id, $verified);

            return response('Webhook processed', 200);
        } catch (\Throwable $e) {
            Log::error('Chapa webhook verification failed', [
                'transaction_id' => $trx->id,
                'exception' => $e,
            ]);

            return response('Verification unavailable', 500);
        }
    }

    protected function verifiedPaymentMatches(Transaction $trx, array $result): bool
    {
        $data = $result['data'] ?? [];

        return ($result['status'] ?? null) === 'success'
            && ($data['status'] ?? null) === 'success'
            && hash_equals((string) $trx->payment_id, (string) ($data['tx_ref'] ?? ''))
            && strtoupper((string) ($data['currency'] ?? '')) === self::CURRENCY
            && round((float) ($data['amount'] ?? -1), 2) === round($this->expectedAmount($trx), 2);
    }

    private function findCustomerTransaction(string $txRef): ?Transaction
    {
        if ($txRef === '' || ! auth()->check()) {
            return null;
        }

        return Transaction::where('payment_gateway_id', $this->paymentGateway->id)
            ->where('user_id', authUser()->id)
            ->where('payment_id', $txRef)
            ->whereIn('status', [Transaction::STATUS_UNPAID, Transaction::STATUS_PAID])
            ->first();
    }

    private function markPaid(int $transactionId, array $verified): ?Transaction
    {
        $trx = Transaction::find($transactionId);
        if (! $trx || ! $trx->isUnpaid() || ! $this->verifiedPaymentMatches($trx, $verified)) {
            return null;
        }

        $data = $verified['data'];
        return app(PaymentSettlementService::class)->settle($trx, [
            'id' => (string) ($data['reference'] ?? $trx->payment_id),
            'local_reference' => (string) $trx->payment_id,
            'gateway_id' => $this->paymentGateway->id,
            'amount' => $data['amount'] ?? null,
            'expected_amount' => $this->expectedAmount($trx),
            'currency' => $data['currency'] ?? null,
            'expected_currency' => self::CURRENCY,
            'payer_id' => $data['reference'] ?? null,
            'payer_email' => $data['email'] ?? null,
        ]);
    }

    private function verifyTransaction(string $txRef): array
    {
        return $this->request('GET', '/transaction/verify/'.rawurlencode($txRef));
    }

    private function request(string $method, string $path, array $options = []): array
    {
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => 'Bearer '.$this->paymentGateway->credentials->secret_key,
            'Accept' => 'application/json',
        ]);

        $response = $this->client->request($method, self::API_URL.$path, $options);
        $result = json_decode((string) $response->getBody(), true);

        if (! is_array($result)) {
            throw new RuntimeException('Invalid response from Chapa');
        }

        return $result;
    }

    private function expectedAmount(Transaction $trx): float
    {
        return round((float) $this->paymentGateway->getChargeAmount($trx->total), 2);
    }

    private function ensureGatewayIsConfigured(): void
    {
        if (! $this->paymentGateway
            || strtoupper((string) $this->paymentGateway->charge_currency) !== self::CURRENCY
            || (float) $this->paymentGateway->charge_rate <= 0
            || empty($this->paymentGateway->credentials->secret_key)) {
            throw new RuntimeException('Chapa is not configured for ETB');
        }
    }

    private function hasValidWebhookSignature(Request $request): bool
    {
        $secret = (string) ($this->paymentGateway->credentials->webhook_secret ?? '');
        if ($secret === '') {
            Log::warning('Chapa webhook secret is not configured');

            return false;
        }

        $signatures = array_filter([
            $request->header('x-chapa-signature'),
            $request->header('chapa-signature'),
        ]);
        $expectedPayloadSignature = hash_hmac('sha256', $request->getContent(), $secret);
        $expectedSecretSignature = hash_hmac('sha256', $secret, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedPayloadSignature, $signature)
                || hash_equals($expectedSecretSignature, $signature)) {
                return true;
            }
        }

        return false;
    }
}
