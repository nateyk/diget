<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GeneralHelperTest extends TestCase
{
    public function test_check_image_size_requires_an_exact_width_and_height_match(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'diget-image-');
        $image = imagecreatetruecolor(120, 120);

        try {
            imagepng($image, $path);

            $this->assertTrue(checkImageSize($path, '120x120'));
            $this->assertFalse(checkImageSize($path, '121x120'));
            $this->assertFalse(checkImageSize($path, '120x121'));
        } finally {
            imagedestroy($image);
            @unlink($path);
        }
    }

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
