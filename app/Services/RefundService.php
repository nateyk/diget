<?php

namespace App\Services;

use App\Events\RefundAccepted;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefundService
{
    public function accept(int $refundId, int $authorId): Refund
    {
        return DB::transaction(function () use ($refundId, $authorId) {
            $refund = Refund::query()
                ->whereKey($refundId)
                ->where('author_id', $authorId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$refund->isPending()) {
                throw new RuntimeException('The refund request has already been processed.');
            }

            $refund->status = Refund::STATUS_ACCEPTED;
            $refund->save();
            event(new RefundAccepted($refund));

            return $refund;
        }, 3);
    }
}
