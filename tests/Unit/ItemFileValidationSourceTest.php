<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ItemFileValidationSourceTest extends TestCase
{
    public function test_item_image_validation_uses_local_file_source_before_public_url(): void
    {
        $root = dirname(__DIR__, 2);
        $uploadedFileModel = file_get_contents($root . '/app/Models/UploadedFile.php');
        $itemController = file_get_contents($root . '/app/Http/Controllers/Workspace/ItemController.php');

        $this->assertStringContainsString('function getFileSource()', $uploadedFileModel);
        $this->assertStringContainsString('public_path($this->path)', $uploadedFileModel);
        $this->assertStringContainsString('storage_path("app/{$this->path}")', $uploadedFileModel);

        $this->assertStringContainsString('Image::make($thumbnail->getFileSource())', $itemController);
        $this->assertStringContainsString('Image::make($previewImage->getFileSource())', $itemController);
        $this->assertStringNotContainsString('Image::make($thumbnail->getFileLink())', $itemController);
        $this->assertStringNotContainsString('Image::make($previewImage->getFileLink())', $itemController);
    }
}
