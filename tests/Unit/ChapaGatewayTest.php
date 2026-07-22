<?php

namespace Tests\Unit;

use App\Http\Controllers\Payments\ChapaController;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

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

    public function test_chapa_http_client_keeps_timeout_and_tls_defaults(): void
    {
        $controller = new ChapaController(null, $this->gateway());
        $property = new ReflectionProperty($controller, 'client');
        $client = $property->getValue($controller);

        $this->assertSame(15, $client->getConfig('timeout'));
        $this->assertSame(5, $client->getConfig('connect_timeout'));
        $this->assertNotFalse($client->getConfig('verify'));
        $this->assertNotSame('', $client->getConfig('verify'));
    }

    public function test_chapa_request_sends_expected_headers_and_json_without_network_access(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'status' => 'success',
                'data' => ['checkout_url' => 'https://checkout.example.test/session'],
            ], JSON_THROW_ON_ERROR)),
        ]));
        $handler->push(Middleware::history($history));

        $controller = new ChapaController(new Client(['handler' => $handler]), $this->gateway());
        $method = new ReflectionMethod($controller, 'request');
        $payload = ['tx_ref' => 'chapa_1000_reference', 'amount' => '100.00'];

        $result = $method->invoke($controller, 'POST', '/transaction/initialize', ['json' => $payload]);

        $this->assertSame('success', $result['status']);
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.chapa.co/v1/transaction/initialize', (string) $request->getUri());
        $this->assertSame('Bearer CHASECK_TEST-example', $request->getHeaderLine('Authorization'));
        $this->assertSame('application/json', $request->getHeaderLine('Accept'));
        $this->assertSame($payload, json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR));
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
