<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentSettlementService;
use CoinGate\Client;
use Illuminate\Http\Request;

class CoingateController extends Controller
{
    private $paymentGateway;
    private $client;

    public function __construct()
    {
        $this->paymentGateway = paymentGateway('coingate');
        if ($this->paymentGateway) {
            $this->client = new Client($this->paymentGateway->credentials->auth_token, true);
        }
    }

    public function process($trx)
    {
        $currency = $this->paymentGateway->getCurrency();

        $body = [
            'title' => translate('Payment for order #:number', [
                'number' => $trx->id,
            ]),
            'order_id' => $trx->id,
            'price_amount' => amountFormat($this->paymentGateway->getChargeAmount($trx->total)),
            'price_currency' => $currency,
            'receive_currency' => $currency,
            'callback_url' => route('payments.webhooks.coingate'),
            'success_url' => route('payments.ipn.coingate', ['id' => hash_encode($trx->id)]),
            'cancel_url' => route('checkout.index', hash_encode($trx->id)),
        ];

        try {
            $order = $this->client->order->create($body);
            if (!$order) {
                throw new Exception(translate('An error occurred while calling the API'));
            }

            $trx->payment_id = $order->id;
            $trx->update();

            $data['type'] = "success";
            $data['method'] = "redirect";
            $data['redirect_url'] = $order->payment_url;
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

            if ($trx->isPaid() && $trx->fulfilled_at) {
                $trx->user->emptyCart();
                return redirect()->route('checkout.index', hash_encode($trx->id));
            }

            sleep(5);
            $attempt++;
        }

        toastr()->warning(translate("Your payment is being processed and you will get email notification when it's completed"));
        return redirect()->route('home');
    }

    public function webhook(Request $request)
    {
        try {
            if ($request->status == 'paid') {
                $trx = Transaction::where('id', $request->order_id)
                    ->where('payment_id', $request->id)
                    ->where('payment_gateway_id', $this->paymentGateway->id)
                    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_UNPAID])
                    ->whereNull('fulfilled_at')->first();

                if ($trx) {
                    $order = $this->client->order->get((int) $request->id);
                    if ($order && strtolower((string) $order->status) === 'paid') {
                        app(PaymentSettlementService::class)->settle($trx, [
                            'id' => (string) $order->id,
                            'gateway_id' => $this->paymentGateway->id,
                            'amount' => $order->price_amount ?? null,
                            'expected_amount' => amountFormat($this->paymentGateway->getChargeAmount($trx->total)),
                            'currency' => $order->price_currency ?? null,
                            'expected_currency' => $this->paymentGateway->getCurrency(),
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
}
