<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentSettlementService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Str;

class PaystackController extends Controller
{
    private $paymentGateway;

    public function __construct()
    {
        $this->paymentGateway = paymentGateway('paystack');
    }

    public function process($trx)
    {
        try {
            $reference = 'pys_' . Str::random(22);

            $data['body'] = [
                'key' => $this->paymentGateway->credentials->public_key,
                'email' => $trx->user->email,
                'amount' => ($this->paymentGateway->getChargeAmount($trx->total) * 100),
                'currency' => $this->paymentGateway->getCurrency(),
                'ref' => $reference,
            ];

            $trx->payment_id = $reference;
            $trx->update();

            $data['type'] = "success";
            $data['method'] = "hosted";
            $data['view'] = 'paystack';
        } catch (\Exception $e) {
            $data['type'] = "error";
            report($e);
            $data['msg'] = translate('Payment initialization failed.');
        }

        return json_encode($data);
    }

    public function ipn(Request $request)
    {
        $reference = $request->reference;

        $trx = Transaction::where('user_id', authUser()->id)
            ->where('payment_id', $reference)
            ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_UNPAID])
            ->firstOrFail();

        $checkoutLink = route('checkout.index', hash_encode($trx->id));

        if ($trx->isPaid()) {
            $trx->user->emptyCart();
            return redirect($checkoutLink);
        }

        try {
            $response = $this->verifyReference($reference);
            $result = json_decode($response, true);

            if (!$result || $result['data']['status'] != "success") {
                toastr()->error(translate('Payment failed'));
                return redirect($checkoutLink);
            }

            $data = $result['data'] ?? [];
            $customer = $data['customer'] ?? [];
            $expectedAmount = $this->paymentGateway->getChargeAmount($trx->total) * 100;
            $providerCurrency = strtoupper((string) ($data['currency'] ?? ''));
            $expectedCurrency = strtoupper((string) $this->paymentGateway->getCurrency());

            if ((string) ($data['reference'] ?? '') !== (string) $reference
                || $providerCurrency !== $expectedCurrency
                || abs((float) ($data['amount'] ?? -1) - (float) $expectedAmount) > 0.00001) {
                toastr()->error(translate('Payment failed'));
                return redirect($checkoutLink);
            }

            app(PaymentSettlementService::class)->settle($trx, [
                'id' => $reference,
                'gateway_id' => $this->paymentGateway->id,
                'amount' => $data['amount'],
                'expected_amount' => $expectedAmount,
                'currency' => $providerCurrency,
                'expected_currency' => $expectedCurrency,
                'payer_id' => $customer['id'] ?? null,
                'payer_email' => $customer['email'] ?? null,
            ]);

            $trx->user->emptyCart();
            return redirect($checkoutLink);
        } catch (\Exception $e) {
            report($e);
            toastr()->error(translate('Payment failed'));
            return redirect($checkoutLink);
        }
    }

    public function webhook(Request $request)
    {
        $headerSignature = $request->header('x-paystack-signature');
        $content = $request->getContent();

        try {
            $signature = hash_hmac('sha512', $content, $this->paymentGateway->credentials->secret_key);
            if ($signature != $headerSignature) {
                return response('Invalid signature', 401);
            }

            $payload = $request->all();
            if (!$payload) {
                return response('Invalid payload', 401);
            }

            if ($payload['event'] == 'charge.success') {
                $data = $payload['data'];
                $trx = Transaction::where('payment_gateway_id', $this->paymentGateway->id)
                    ->where('payment_id', $data['reference'])
                    ->unpaid()->first();
                if ($trx) {
                    $verified = json_decode($this->verifyReference($data['reference']), true);
                    $verifiedData = $verified['data'] ?? [];
                    $customer = $verifiedData['customer'] ?? ($data['customer'] ?? []);
                    $expectedAmount = $this->paymentGateway->getChargeAmount($trx->total) * 100;
                    $expectedCurrency = strtoupper((string) $this->paymentGateway->getCurrency());
                    if (($verifiedData['status'] ?? null) === 'success'
                        && (string) ($verifiedData['reference'] ?? '') === (string) $data['reference']
                        && strtoupper((string) ($verifiedData['currency'] ?? '')) === $expectedCurrency
                        && abs((float) ($verifiedData['amount'] ?? -1) - (float) $expectedAmount) <= 0.00001) {
                        app(PaymentSettlementService::class)->settle($trx, [
                            'id' => $data['reference'],
                            'gateway_id' => $this->paymentGateway->id,
                            'amount' => $verifiedData['amount'],
                            'expected_amount' => $expectedAmount,
                            'currency' => $verifiedData['currency'],
                            'expected_currency' => $expectedCurrency,
                            'payer_id' => $customer['id'] ?? null,
                            'payer_email' => $customer['email'] ?? null,
                        ]);
                    }
                }
            }

            return response('Webhook processed successfully', 200);
        } catch (\Exception $e) {
            report($e);
            return response('Webhook processing failed', 500);
        }
    }

    private function verifyReference($reference)
    {
        $client = new Client();
        $paystackSecretKey = $this->paymentGateway->credentials->secret_key;
        $response = $client->request('GET', 'https://api.paystack.co/transaction/verify/' . $reference, [
            'headers' => [
                'Authorization' => 'Bearer ' . $paystackSecretKey,
            ],
        ]);
        return $response->getBody();
    }
}
