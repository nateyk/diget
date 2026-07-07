<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GeneralHelperTest extends TestCase
{
    public function test_get_ip_falls_back_when_remote_addr_is_missing(): void
    {
        $originalRemoteAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        try {
            unset($_SERVER['REMOTE_ADDR']);
            unset($_SERVER['HTTP_CF_CONNECTING_IP']);
            unset($_SERVER['HTTP_X_FORWARDED_FOR']);
            unset($_SERVER['HTTP_CLIENT_IP']);

            $this->assertSame('127.0.0.1', getIp());
        } finally {
            if ($originalRemoteAddress !== null) {
                $_SERVER['REMOTE_ADDR'] = $originalRemoteAddress;
            }
        }
    }
}
