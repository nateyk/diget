<?php

namespace App\Services;

use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WithdrawalService
{
    public function submit(int $authorId): Withdrawal
    {
        return DB::transaction(function () use ($authorId) {
            $author = DB::table('users')->where('id', $authorId)->lockForUpdate()->first();
            if (!$author || (float) $author->balance <= 0) {
                throw new RuntimeException('The account balance is not available.');
            }

            $method = DB::table('withdrawal_methods')
                ->where('id', $author->withdrawal_method_id)
                ->where('status', 1)
                ->lockForUpdate()
                ->first();
            if (!$method) {
                throw new RuntimeException('A withdrawal method is required.');
            }

            if ((float) $author->balance < (float) $method->minimum) {
                throw new RuntimeException('Your balance is less than the minimum withdrawal limit.');
            }

            if (Withdrawal::query()->where('author_id', $authorId)->pending()->exists()) {
                throw new RuntimeException('A withdrawal is already pending.');
            }

            $amount = (float) $author->balance;
            $withdrawal = Withdrawal::create([
                'author_id' => $authorId,
                'amount' => $amount,
                'method' => $method->name,
                'account' => $author->withdrawal_account,
                'status' => Withdrawal::STATUS_PENDING,
            ]);

            DB::table('users')->where('id', $authorId)->update([
                'balance' => 0,
                'updated_at' => now(),
            ]);

            return $withdrawal;
        }, 3);
    }

    public function transition(Withdrawal $withdrawal, int $newStatus): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $newStatus) {
            $locked = Withdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            $terminal = [Withdrawal::STATUS_RETURNED, Withdrawal::STATUS_COMPLETED, Withdrawal::STATUS_CANCELLED];
            if (in_array($locked->status, $terminal, true)) {
                throw new RuntimeException('The withdrawal has already reached a final status.');
            }

            $allowed = match ((int) $locked->status) {
                Withdrawal::STATUS_PENDING => [Withdrawal::STATUS_APPROVED, Withdrawal::STATUS_RETURNED, Withdrawal::STATUS_CANCELLED],
                Withdrawal::STATUS_APPROVED => [Withdrawal::STATUS_COMPLETED, Withdrawal::STATUS_RETURNED, Withdrawal::STATUS_CANCELLED],
                default => [],
            };
            if (!in_array($newStatus, $allowed, true)) {
                throw new RuntimeException('The withdrawal status transition is not allowed.');
            }

            if ($newStatus === Withdrawal::STATUS_RETURNED) {
                DB::table('users')->where('id', $locked->author_id)->lockForUpdate()->increment('balance', $locked->amount);
            }

            $locked->status = $newStatus;
            $locked->save();

            return $locked->fresh('author');
        }, 3);
    }
}
