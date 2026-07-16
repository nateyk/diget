<?php

namespace App\Listeners;

use App\Events\RefundAccepted;
use App\Events\SaleRefunded;
use App\Jobs\SendRefundAcceptedNotification;
use App\Models\Refund;

class ProcessAcceptedRefund
{
    public function handle(RefundAccepted $event)
    {
        $refund = Refund::query()->with('purchase.sale')->whereKey($event->refund->getKey())->first();
        if (!$refund || !$refund->isAccepted()) {
            return;
        }

        $sale = $refund->purchase?->sale;
        if ($sale && !$sale->isRefunded()) {
            event(new SaleRefunded($sale));
        }

        dispatch(new SendRefundAcceptedNotification($refund))->afterCommit();
    }
}
