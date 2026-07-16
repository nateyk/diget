<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentSettlementService;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    public function process($trx)
    {
        try {
            DB::transaction(function () use ($trx) {
                $trx = Transaction::query()->whereKey($trx->id)->lockForUpdate()->firstOrFail();
                if (!$trx->isUnpaid() || $trx->fulfilled_at) {
                    return;
                }
                $user = $trx->user()->lockForUpdate()->firstOrFail();
                if ($user->balance < $trx->total) {
                    throw new \RuntimeException(translate('Your account balance is insufficient'));
                }

                $user->decrement('balance', $trx->total);
                app(PaymentSettlementService::class)->settle($trx, [
                    'id' => 'balance-' . $trx->id,
                    'gateway_id' => $trx->payment_gateway_id,
                ]);
            }, 3);

            $user->emptyCart();
            $data['type'] = 'success';
        } catch (\Exception $e) {
            $data['type'] = "error";
            $data['msg'] = $e instanceof \RuntimeException ? $e->getMessage() : translate('Payment failed');
            return json_encode($data);
        }

        return json_encode($data);
    }
}
