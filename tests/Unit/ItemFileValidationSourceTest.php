<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ItemFileValidationSourceTest extends TestCase
{
    public function test_item_image_validation_reads_from_uploaded_file_source(): void
    {
        $root = dirname(__DIR__, 2);
        $itemController = file_get_contents($root . '/app/Http/Controllers/Workspace/ItemController.php');

        $this->assertStringContainsString('ImageManager', $itemController);
        $this->assertStringContainsString('$manager->read($thumbnail->getFileSource())', $itemController);
        $this->assertStringContainsString('$manager->read($previewImage->getFileSource())', $itemController);
        $this->assertStringNotContainsString('getFileLink())', $itemController);
    }
}
