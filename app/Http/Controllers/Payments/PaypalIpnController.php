<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentSettlementService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaypalIpnController extends Controller
{
    private $paymentGateway;
    private $endpoint;

    public function __construct()
    {
        $this->paymentGateway = paymentGateway('paypal_ipn');
        if ($this->paymentGateway) {
            $this->endpoint = $this->paymentGateway->isSandboxMode() ?
            'https://www.sandbox.paypal.com/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        }
    }

    public function process($trx)
    {
        $gateway = $this->paymentGateway;

        $amount = amountFormat($gateway->getChargeAmount($trx->amount));
        $fees = amountFormat($gateway->getChargeAmount($trx->fees));
        $tax = $trx->tax ? amountFormat($gateway->getChargeAmount($trx->tax->amount)) : 0;
        $total = amountFormat($amount + $fees + $tax);

        $body = [
            'cmd' => '_xclick',
            'business' => trim($this->paymentGateway->credentials->email),
            'cbt' => @settings('general')->site_name,
            'currency_code' => $this->paymentGateway->getCurrency(),
            'quantity' => 1,
            'item_name' => translate('Payment for order No.:number', [
                'number' => $trx->id,
            ]),
            'custom' => "$trx->id",
            'amount' => $amount,
            'tax' => $tax,
            'handling' => $fees,
            'return' => route('payments.ipn.paypal-ipn', ['id' => hash_encode($trx->id)]),
            'cancel_return' => route('checkout.index', hash_encode($trx->id)),
            'notify_url' => route('payments.notifications.paypal-ipn'),
        ];

        try {
            $response = Http::asForm()->post($this->endpoint, $body);

            if (!$response->successful()) {
                throw new Exception(translate('An error occurred while calling the API'));
            }

            $redirectUrl = $this->endpoint . '?' . http_build_query($body);

            $data['type'] = "success";
            $data['method'] = "redirect";
            $data['redirect_url'] = $redirectUrl;
        } catch (\Exception $e) {
            $data['type'] = "error";
            report($e);
            $data['msg'] = translate('Payment initialization failed.');
        }

        return json_encode($data);
    }

    public function ipn(Request $request)
    {
        $maxAttempts = 10;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $trx = Transaction::where('id', hash_decode($request->id))
                ->where('user_id', authUser()->id)
                ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_UNPAID])
                ->firstOrFail();

            if ($trx->isPaid()) {
                $trx->user->emptyCart();
                return redirect()->route('checkout.index', hash_encode($trx->id));
            }

            sleep(5);
            $attempt++;
        }

        toastr()->warning(translate("Your payment is being processed and you will get email notification when it's completed"));
        return redirect()->route('home');
    }

    public function notification(Request $request)
    {
        try {
            $payload = $request->all();
            if (!$payload) {
                return response('Invalid payload', 401);
            }

            $payload['cmd'] = '_notify-validate';

            $response = Http::asForm()->post(
                $this->endpoint,
                $payload
            );

            if ($response->body() === 'VERIFIED') {
                $paymentStatus = $payload['payment_status'] ?? null;
                $trxId = $payload['custom'] ?? null;
                $paymentId = $payload['txn_id'] ?? null;
                $payerId = $payload['payer_id'] ?? null;
                $payerEmail = $payload['payer_email'] ?? null;

                if ($paymentStatus === 'Completed' && $paymentId && ctype_digit((string) $trxId)) {
                    $trx = Transaction::where('id', (int) $trxId)
                        ->where('payment_gateway_id', $this->paymentGateway->id)
                        ->unpaid()->first();
                    if ($trx && !Transaction::where('payment_id', $paymentId)->exists()) {
                        $expectedAmount = $this->expectedAmount($trx);
                        $expectedCurrency = strtoupper((string) $this->paymentGateway->getCurrency());
                        $receiver = strtolower(trim((string) ($payload['receiver_email'] ?? $payload['business'] ?? '')));
                        $configuredReceiver = strtolower(trim((string) $this->paymentGateway->credentials->email));
                        $merchantMatches = $receiver !== '' && hash_equals($configuredReceiver, $receiver);
                        $currencyMatches = strtoupper((string) ($payload['mc_currency'] ?? '')) === $expectedCurrency;
                        $amountMatches = abs((float) ($payload['mc_gross'] ?? -1) - $expectedAmount) <= 0.00001;
                        if ($merchantMatches && $currencyMatches && $amountMatches) {
                            app(PaymentSettlementService::class)->settle($trx, [
                                'id' => $paymentId,
                                'gateway_id' => $this->paymentGateway->id,
                                'amount' => $payload['mc_gross'],
                                'expected_amount' => $expectedAmount,
                                'currency' => $payload['mc_currency'],
                                'expected_currency' => $expectedCurrency,
                                'payer_id' => $payerId,
                                'payer_email' => $payerEmail,
                            ]);
                        }
                    }
                }

                return response('Notification Verified', 200);
            } elseif ($response->body() === 'INVALID') {
                return response('Notification Invalid', 400);
            } else {
                return response('Notification Error', 500);
            }
        } catch (Exception $e) {
            return response('Notification Error', 500);
        }
    }

    private function expectedAmount(Transaction $trx): float
    {
        $gateway = $this->paymentGateway;
        $amount = amountFormat($gateway->getChargeAmount($trx->amount));
        $fees = amountFormat($gateway->getChargeAmount($trx->fees));
        $tax = $trx->tax ? amountFormat($gateway->getChargeAmount($trx->tax->amount)) : 0;
        return (float) amountFormat($amount + $fees + $tax);
    }
}
