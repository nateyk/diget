<?php

namespace Tests\Unit;

use App\Models\Refund;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class FinancialConcurrencyTest extends TestCase
{
    /** @group concurrency */
    public function test_payment_settlement_fulfils_a_deposit_once_under_concurrent_callbacks(): void
    {
        $userId = $this->createUser('settlement');
        $transactionId = DB::table('transactions')->insertGetId([
            'user_id' => $userId,
            'amount' => 10,
            'fees' => 0,
            'total' => 10,
            'type' => Transaction::TYPE_DEPOSIT,
            'status' => Transaction::STATUS_UNPAID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $results = $this->runWorkers([
                ['settle', $transactionId, 'provider-' . $transactionId],
                ['settle', $transactionId, 'provider-' . $transactionId],
            ]);

            self::assertSame(2, count($results));
            self::assertTrue(count(array_filter($results, fn (array $result): bool => $result['ok'] ?? false)) > 0, json_encode($results));
            self::assertSame(1, DB::table('transactions')->where('id', $transactionId)->whereNotNull('fulfilled_at')->count());
            self::assertSame(10.0, (float) DB::table('users')->where('id', $userId)->value('balance'));
            self::assertSame('provider-' . $transactionId, DB::table('transactions')->where('id', $transactionId)->value('payment_id'));
        } finally {
            DB::table('transactions')->where('id', $transactionId)->delete();
            DB::table('users')->where('id', $userId)->delete();
        }
    }

    /** @group concurrency */
    public function test_withdrawal_submission_allows_one_pending_withdrawal_under_concurrent_requests(): void
    {
        $methodId = DB::table('withdrawal_methods')->insertGetId([
            'name' => 'Concurrency ' . uniqid(),
            'minimum' => 10,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId = $this->createUser('withdrawal', ['balance' => 50, 'withdrawal_method_id' => $methodId, 'withdrawal_account' => 'test-account']);

        try {
            $this->runWorkers([
                ['withdraw', $userId],
                ['withdraw', $userId],
            ]);

            self::assertSame(1, Withdrawal::query()->where('author_id', $userId)->pending()->count());
            self::assertSame(0.0, (float) DB::table('users')->where('id', $userId)->value('balance'));
        } finally {
            DB::table('withdrawals')->where('author_id', $userId)->delete();
            DB::table('users')->where('id', $userId)->delete();
            DB::table('withdrawal_methods')->where('id', $methodId)->delete();
        }
    }

    /** @group concurrency */
    public function test_refund_acceptance_transitions_once_under_concurrent_requests(): void
    {
        [$buyerId, $authorId, $itemId, $saleId, $purchaseId] = $this->createPurchaseFixture();
        $refundId = DB::table('refunds')->insertGetId([
            'user_id' => $buyerId,
            'author_id' => $authorId,
            'purchase_id' => $purchaseId,
            'status' => Refund::STATUS_PENDING,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->runWorkers([
                ['refund', $refundId, $authorId],
                ['refund', $refundId, $authorId],
            ]);

            self::assertSame(Refund::STATUS_ACCEPTED, (int) DB::table('refunds')->where('id', $refundId)->value('status'));
            self::assertSame(1, DB::table('refunds')->where('id', $refundId)->count());
        } finally {
            DB::table('refunds')->where('id', $refundId)->delete();
            DB::table('purchases')->where('id', $purchaseId)->delete();
            DB::table('sales')->where('id', $saleId)->delete();
            DB::table('items')->where('id', $itemId)->delete();
            DB::table('categories')->where('id', $this->categoryId)->delete();
            DB::table('users')->whereIn('id', [$buyerId, $authorId])->delete();
        }
    }

    private int $categoryId;

    private function createPurchaseFixture(): array
    {
        $buyerId = $this->createUser('buyer');
        $authorId = $this->createUser('author');
        $this->categoryId = DB::table('categories')->insertGetId([
            'name' => 'Concurrency ' . uniqid(),
            'slug' => 'concurrency-' . uniqid(),
            'main_file_types' => 'zip',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $itemId = DB::table('items')->insertGetId([
            'author_id' => $authorId,
            'name' => 'Concurrency Item ' . uniqid(),
            'slug' => 'concurrency-' . uniqid(),
            'description' => 'Concurrency fixture',
            'category_id' => $this->categoryId,
            'tags' => 'test',
            'main_file' => 'fixture.zip',
            'regular_price' => 10,
            'extended_price' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $saleId = DB::table('sales')->insertGetId([
            'author_id' => $authorId,
            'user_id' => $buyerId,
            'item_id' => $itemId,
            'license_type' => 1,
            'price' => 10,
            'author_earning' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $purchaseId = DB::table('purchases')->insertGetId([
            'user_id' => $buyerId,
            'author_id' => $authorId,
            'sale_id' => $saleId,
            'item_id' => $itemId,
            'license_type' => 1,
            'code' => 'concurrency-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$buyerId, $authorId, $itemId, $saleId, $purchaseId];
    }

    private function createUser(string $prefix, array $attributes = []): int
    {
        return DB::table('users')->insertGetId(array_merge([
            'username' => $prefix . '-' . uniqid(),
            'email' => $prefix . '-' . uniqid() . '@example.test',
            'is_author' => $prefix !== 'buyer',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    private function runWorkers(array $operations): array
    {
        $processes = [];
        foreach ($operations as $operation) {
            $process = new Process([
                PHP_BINARY,
                base_path('tests/Support/financial_concurrency_worker.php'),
                (string) $operation[0],
                '',
                ...array_map('strval', array_slice($operation, 1)),
            ], base_path(), ['APP_ENV' => 'testing']);
            $process->start();
            $processes[] = $process;
        }

        $results = [];
        foreach ($processes as $process) {
            $process->wait();
            $lines = array_values(array_filter(explode(PHP_EOL, trim($process->getOutput()))));
            $results[] = json_decode(end($lines), true) ?: ['ok' => false, 'output' => $process->getErrorOutput()];
        }

        return $results;
    }
}
