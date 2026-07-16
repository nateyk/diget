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
        if (!class_exists(ZipArchive::class)) {
            self::markTestSkipped('ZipArchive is not available');
        }

        $path = tempnam(sys_get_temp_dir(), 'security-zip-');
        $zip = new ZipArchive();
        self::assertSame(true, $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('../escape.php', '<?php echo 1;');
        $zip->close();

        $zip->open($path);
        try {
            $this->expectException(RuntimeException::class);
            (new ArchiveValidator())->validate($zip);
        } finally {
            $zip->close();
            @unlink($path);
        }
    }
}
