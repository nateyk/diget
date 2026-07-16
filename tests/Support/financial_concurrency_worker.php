<?php

use App\Events\RefundAccepted;
use App\Models\Refund;
use App\Models\Transaction;
use App\Services\PaymentSettlementService;
use App\Services\RefundService;
use App\Services\WithdrawalService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Event;

$_SERVER['APP_ENV'] = 'testing';
$_ENV['APP_ENV'] = 'testing';

$basePath = dirname(__DIR__, 2);
require $basePath . '/vendor/autoload.php';

$app = require $basePath . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$operation = $argv[1] ?? '';
$barrier = $argv[2] ?? '';

if ($barrier !== '') {
    file_put_contents($barrier . '.' . getmypid(), 'ready');
    $deadline = microtime(true) + 10;
    while (!file_exists($barrier . '.go') && microtime(true) < $deadline) {
        usleep(10000);
    }
}

try {
    $result = match ($operation) {
        'settle' => app(PaymentSettlementService::class)->settle(
            Transaction::query()->findOrFail((int) $argv[3]),
            ['id' => (string) $argv[4], 'allow_pending' => false]
        ),
        'withdraw' => app(WithdrawalService::class)->submit((int) $argv[3]),
        'refund' => (function () use ($argv) {
            Event::fake([RefundAccepted::class]);
            return app(RefundService::class)->accept((int) $argv[3], (int) $argv[4]);
        })(),
        default => throw new InvalidArgumentException('Unknown concurrency operation.'),
    };

    echo json_encode(['ok' => true, 'id' => $result->getKey()]) . PHP_EOL;
} catch (Throwable $exception) {
    echo json_encode(['ok' => false, 'type' => get_class($exception), 'message' => $exception->getMessage()]) . PHP_EOL;
    exit(1);
}
