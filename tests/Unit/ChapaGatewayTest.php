<?php

namespace Tests\Unit;

use App\Http\Controllers\Payments\ChapaController;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ChapaGatewayTest extends TestCase
{
    private function controller(PaymentGateway $gateway): ChapaController
    {
        return new class(new Client, $gateway) extends ChapaController
        {
            public function matches(Transaction $transaction, array $result): bool
            {
                return $this->verifiedPaymentMatches($transaction, $result);
            }
        };
    }

    private function gateway(): PaymentGateway
    {
        $gateway = new PaymentGateway;
        $gateway->exists = true;
        $gateway->charge_currency = 'ETB';
        $gateway->charge_rate = 2;
        $gateway->credentials = (object) [
            'secret_key' => 'CHASECK_TEST-example',
            'webhook_secret' => 'webhook-secret',
        ];

        return $gateway;
    }

    private function transaction(): Transaction
    {
        $transaction = new Transaction;
        $transaction->total = 50;
        $transaction->payment_id = 'chapa_1000_reference';

        return $transaction;
    }

    private function verifiedData(array $overrides = []): array
    {
        return [
            'status' => 'success',
            'data' => array_merge([
                'status' => 'success',
                'tx_ref' => 'chapa_1000_reference',
                'currency' => 'ETB',
                'amount' => 100,
            ], $overrides),
        ];
    }

    public function test_verified_etb_payment_must_match_server_transaction(): void
    {
        $controller = $this->controller($this->gateway());

        $this->assertTrue($controller->matches($this->transaction(), $this->verifiedData()));
    }

    #[DataProvider('invalidVerificationProvider')]
    public function test_verification_rejects_untrusted_or_mismatched_data(array $result): void
    {
        $controller = $this->controller($this->gateway());

        $this->assertFalse($controller->matches($this->transaction(), $result));
    }

    public static function invalidVerificationProvider(): array
    {
        return [
            'failed API response' => [['status' => 'failed', 'data' => []]],
            'failed payment status' => [self::verificationResult(['status' => 'failed'])],
            'wrong reference' => [self::verificationResult(['tx_ref' => 'attacker-reference'])],
            'wrong currency' => [self::verificationResult(['currency' => 'USD'])],
            'wrong amount' => [self::verificationResult(['amount' => 1])],
        ];
    }

    private static function verificationResult(array $overrides): array
    {
        return [
            'status' => 'success',
            'data' => array_merge([
                'status' => 'success',
                'tx_ref' => 'chapa_1000_reference',
                'currency' => 'ETB',
                'amount' => 100,
            ], $overrides),
        ];
    }
}
