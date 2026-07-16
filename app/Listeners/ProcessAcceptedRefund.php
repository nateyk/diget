<?php

namespace App\Listeners;

use App\Events\RefundAccepted;
use App\Events\SaleRefunded;
use App\Jobs\SendRefundAcceptedNotification;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;

class ProcessAcceptedRefund
{
    public function handle(RefundAccepted $event)
    {
        $refund = DB::transaction(function () use ($event) {
            $refund = Refund::query()->with('purchase.sale')->whereKey($event->refund->getKey())->lockForUpdate()->first();
            if (!$refund || !$refund->isAccepted()) {
                return null;
            }

            $sale = $refund->purchase?->sale;
            if (!$sale || $sale->isRefunded()) {
                return $refund;
            }

            event(new SaleRefunded($sale));
            return $refund;
        });

        if ($refund) {
            dispatch(new SendRefundAcceptedNotification($refund))->afterCommit();
        }
    }
}
