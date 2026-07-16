<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Mollie\Laravel\Facades\Mollie;

class MollieController extends Controller
{
    private $paymentGateway;
    private $mollieApi;

    public function __construct()
    {
        $this->paymentGateway = paymentGateway('mollie');
        if ($this->paymentGateway) {
            Config::set(['mollie.key' => trim($this->paymentGateway->credentials->api_key)]);
            $this->mollieApi = Mollie::api();
        }
    }

    public function process($trx)
    {
        $body = [
            'amount' => [
                'currency' => $this->paymentGateway->getCurrency(),
                'value' => amountFormat($this->paymentGateway->getChargeAmount($trx->total)),
            ],
            'description' => translate('Payment for order #:number', [
                'number' => $trx->id,
            ]),
            "metadata" => [
                "trx_id" => $trx->id,
            ],
            'redirectUrl' => route('payments.ipn.mollie', 'id=' . hash_encode($trx->id)),
            'cancelUrl' => route('checkout.index', hash_encode($trx->id)),
            'webhookUrl' => route('payments.webhooks.mollie'),
        ];

        try {
            $payment = $this->mollieApi->payments->create($body);
            $payment = $this->mollieApi->payments->get($payment->id);

            $trx->payment_id = $payment->id;
            $trx->update();

            $data['type'] = "success";
            $data['method'] = "redirect";
            $data['redirect_url'] = $payment->getCheckoutUrl();
        } catch (\Exception $e) {
            $data['type'] = "error";
            report($e);
            $data['msg'] = translate('Payment initialization failed.');
        }

        return json_encode($data);
    }

    public function ipn(Request $request)
    {
        $trx = Transaction::where('id', hash_decode($request->id))
            ->where('user_id', authUser()->id)
            ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_UNPAID])
            ->firstOrFail();

        $checkoutLink = route('checkout.index', hash_encode($trx->id));

        if ($trx->isPaid() && $trx->fulfilled_at) {
            $trx->user->emptyCart();
            return redirect($checkoutLink);
        }

        try {
            $payment = $this->mollieApi->payments->get($trx->payment_id);
            if ($payment->status != "paid") {
                toastr()->error(translate('Payment failed'));
                return redirect($checkoutLink);
            }

            app(PaymentSettlementService::class)->settle($trx, [
                'id' => $payment->id,
                'gateway_id' => $this->paymentGateway->id,
                'amount' => $payment->amount->value ?? null,
                'expected_amount' => amountFormat($this->paymentGateway->getChargeAmount($trx->total)),
                'currency' => $payment->amount->currency ?? null,
                'expected_currency' => $this->paymentGateway->getCurrency(),
                'payer_id' => $payment->profileId,
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
        $paymentId = $request->id;

        try {
            $payment = $this->mollieApi->payments->get($paymentId);
            if ($payment) {
                if ($payment->status != "paid") {
                    return response('Payment failed', 400);
                }

                $trx = Transaction::where('id', $payment->metadata->trx_id)
                    ->where('payment_id', $payment->id)
                    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_UNPAID])
                    ->whereNull('fulfilled_at')->first();
                if ($trx) {
                    app(PaymentSettlementService::class)->settle($trx, [
                        'id' => $payment->id,
                        'gateway_id' => $this->paymentGateway->id,
                        'amount' => $payment->amount->value ?? null,
                        'expected_amount' => amountFormat($this->paymentGateway->getChargeAmount($trx->total)),
                        'currency' => $payment->amount->currency ?? null,
                        'expected_currency' => $this->paymentGateway->getCurrency(),
                        'payer_id' => $payment->profileId,
                    ]);
                }
            }

            return response('Webhook processed successfully', 200);
        } catch (\Exception $e) {
            report($e);
            return response('Webhook processing failed', 500);
        }
    }
}
