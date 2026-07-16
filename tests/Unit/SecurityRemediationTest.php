<?php

namespace Tests\Unit;

use App\Services\ArchiveValidator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ZipArchive;

class SecurityRemediationTest extends TestCase
{
    private function source(string $path): string
    {
        return file_get_contents(dirname(__DIR__, 2) . '/' . $path);
    }

    public function test_payment_settlement_is_locked_and_idempotent(): void
    {
        $source = $this->source('app/Services/PaymentSettlementService.php');
        self::assertStringContainsString('lockForUpdate()', $source);
        self::assertStringContainsString('fulfilled_at', $source);
        self::assertStringContainsString('DB::transaction', $source);
        self::assertStringContainsString('whereKeyNot($locked->getKey())', $source);
        self::assertStringContainsString('The provider payment identifier has already been used.', $source);
        self::assertStringContainsString("'local_reference'", $source);
        self::assertStringContainsString('normalizeMoney', $source);
        self::assertStringNotContainsString('abs((float) $actual - (float) $expected)', $source);
        self::assertStringContainsString('!$locked->isUnpaid() && !$locked->isPaid()', $source);
    }

    public function test_provider_callbacks_bind_verified_payment_values(): void
    {
        $paystack = $this->source('app/Http/Controllers/Payments/PaystackController.php');
        self::assertStringContainsString("expectedAmount", $paystack);
        self::assertStringContainsString("expectedCurrency", $paystack);
        self::assertStringContainsString("data['reference']", $paystack);

        $paypal = $this->source('app/Http/Controllers/Payments/PaypalIpnController.php');
        self::assertStringContainsString('receiver_email', $paypal);
        self::assertStringContainsString('mc_gross', $paypal);
        self::assertStringContainsString('mc_currency', $paypal);
    }

    public function test_ownership_and_session_guards_are_enforced(): void
    {
        $ticket = $this->source('app/Http/Controllers/Workspace/TicketController.php');
        self::assertStringContainsString("whereHas('reply.ticket'", $ticket);

        $twoFactor = $this->source('app/Http/Middleware/TwoFactorVerify.php');
        self::assertStringContainsString("['session_id']", $twoFactor);
        self::assertStringContainsString("['user_id']", $twoFactor);
    }

    public function test_refund_side_effects_are_dispatched_after_commit(): void
    {
        $refundService = $this->source('app/Services/RefundService.php');
        self::assertStringContainsString('DB::afterCommit', $refundService);
        self::assertStringContainsString('event(new RefundAccepted($refund))', $refundService);

        $acceptedRefundListener = $this->source('app/Listeners/ProcessAcceptedRefund.php');
        self::assertStringNotContainsString('DB::transaction', $acceptedRefundListener);
        self::assertStringContainsString('event(new SaleRefunded($sale))', $acceptedRefundListener);
    }

    public function test_gateway_callbacks_can_recover_paid_unfulfilled_transactions(): void
    {
        foreach (glob(dirname(__DIR__, 2) . '/app/Http/Controllers/Payments/*Controller.php') as $controller) {
            $name = basename($controller);
            $source = file_get_contents($controller);

            if ($name !== 'BankwireController.php') {
                self::assertStringNotContainsString('->unpaid()->first()', $source, $name);
            }

            self::assertStringNotContainsString('if ($trx->isPaid())', $source, $name);
            self::assertStringNotContainsString('if (! $trx || $trx->isPaid())', $source, $name);
        }
    }

    public function test_cron_and_editor_uploads_are_protected(): void
    {
        $cron = $this->source('app/Http/Controllers/CronJobController.php');
        self::assertStringContainsString("\$configuredKey === ''", $cron);
        self::assertStringContainsString('hash_equals', $cron);

        $routes = $this->source('routes/web.php');
        self::assertStringContainsString("throttle:20,1", $routes);
    }

    public function test_archive_validator_rejects_traversal(): void
    {
        self::assertTrue(class_exists(ZipArchive::class), 'The ZIP extension must be enabled for security tests.');
        $zip = $this->zipWithEntries(['../escape.php' => '<?php echo 1;']);
        try {
            $this->expectException(RuntimeException::class);
            (new ArchiveValidator())->validate($zip);
        } finally {
            $zip->close();
        }
    }

    /**
     * @dataProvider forbiddenArchivePathProvider
     */
    public function test_archive_validator_rejects_absolute_and_windows_paths(string $entry): void
    {
        $zip = $this->zipWithEntries([$entry => 'payload']);
        try {
            $this->expectException(RuntimeException::class);
            (new ArchiveValidator())->validate($zip);
        } finally {
            $zip->close();
        }
    }

    public static function forbiddenArchivePathProvider(): array
    {
        return [['/etc/passwd'], ['C:\\Windows\\system32\\drivers\\etc\\hosts'], ['folder/../../escape.txt']];
    }

    public function test_archive_validator_rejects_duplicate_normalized_paths(): void
    {
        $zip = $this->zipWithEntries(['assets/app.js' => 'one', 'assets\\app.js' => 'two']);
        try {
            $this->expectException(RuntimeException::class);
            (new ArchiveValidator())->validate($zip);
        } finally {
            $zip->close();
        }
    }

    public function test_archive_validator_rejects_excessive_file_count(): void
    {
        $entries = [];
        for ($index = 0; $index < 1001; $index++) {
            $entries["files/{$index}.txt"] = 'x';
        }

        $zip = $this->zipWithEntries($entries);
        try {
            $this->expectException(RuntimeException::class);
            (new ArchiveValidator())->validate($zip);
        } finally {
            $zip->close();
        }
    }

    public function test_archive_validator_rejects_unsafe_compression_ratio(): void
    {
        $zip = $this->zipWithEntries(['zeros.bin' => str_repeat("\0", 1024 * 1024)]);
        try {
            $this->expectException(RuntimeException::class);
            (new ArchiveValidator())->validate($zip);
        } finally {
            $zip->close();
        }
    }

    public function test_archive_validator_rejects_unix_symlinks(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'security-zip-');
        $zip = new ZipArchive();
        self::assertSame(true, $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('link', 'target.txt');
        self::assertTrue($zip->setExternalAttributesName('link', ZipArchive::OPSYS_UNIX, (0120000 | 0777) << 16));
        $zip->close();
        self::assertSame(true, $zip->open($path));
        try {
            $this->expectException(RuntimeException::class);
            (new ArchiveValidator())->validate($zip);
        } finally {
            $zip->close();
            @unlink($path);
        }
    }

    public function test_archive_validator_accepts_valid_theme_and_addon_shapes(): void
    {
        $zip = $this->zipWithEntries([
            'config.json' => json_encode([
                'type' => 'theme',
                'path' => 'themes/example',
                'copy' => ['directories' => ['resources'], 'files' => ['config.json']],
                'general_files' => ['copy' => ['files' => [['root' => 'resources/app.js', 'destination' => 'public/app.js']]]],
            ]),
            'resources/app.js' => 'payload',
        ]);
        try {
            (new ArchiveValidator())->validate($zip);
            (new ArchiveValidator())->validateConfigPaths(json_decode($zip->getFromName('config.json'), true));
            self::assertTrue(true);
        } finally {
            $zip->close();
        }
    }

    public function test_archive_validator_rejects_manifest_paths_in_nested_addon_sections(): void
    {
        $this->expectException(RuntimeException::class);
        (new ArchiveValidator())->validateConfigPaths([
            'path' => 'addons/example',
            'general_files' => ['copy' => ['files' => [['root' => 'safe.txt', 'destination' => '..\\app.php']]]],
        ]);
    }

    public function test_archive_uploaders_validate_before_extract_and_manifest_mutation(): void
    {
        foreach ([
            'app/Http/Controllers/Admin/Appearance/ThemeController.php',
            'app/Http/Controllers/Admin/System/AddonController.php',
        ] as $controller) {
            $source = $this->source($controller);
            self::assertStringContainsString('app(ArchiveValidator::class)->validate($zip);', $source);
            self::assertStringContainsString('validateConfigPaths($config ?: []);', $source);
            self::assertLessThan(strpos($source, 'extractTo('), strpos($source, 'validate($zip);'));
        }
    }

    public function test_archive_manifest_paths_are_validated(): void
    {
        $source = $this->source('app/Services/ArchiveValidator.php');
        self::assertStringContainsString('validateConfigPaths', $source);
        self::assertStringContainsString('forbidden path', $source);
    }

    private function zipWithEntries(array $entries): ZipArchive
    {
        $path = tempnam(sys_get_temp_dir(), 'security-zip-');
        $zip = new ZipArchive();
        self::assertSame(true, $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        foreach ($entries as $name => $contents) {
            $zip->addFromString($name, $contents);
        }
        $zip->close();
        self::assertSame(true, $zip->open($path));
        return $zip;
    }
}
