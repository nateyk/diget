<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class ArchiveValidator
{
    public function validate(ZipArchive $zip): void
    {
        $count = $zip->numFiles;
        if ($count < 1 || $count > 1000) {
            throw new RuntimeException('The archive contains an invalid number of files.');
        }

        $compressed = 0;
        $uncompressed = 0;
        $paths = [];
        for ($index = 0; $index < $count; $index++) {
            $name = $zip->getNameIndex($index);
            if (!is_string($name) || $name === '' || str_contains($name, "\0")) {
                throw new RuntimeException('The archive contains an invalid path.');
            }

            $normalized = str_replace('\\', '/', $name);
            if (str_starts_with($normalized, '/')
                || preg_match('/^[A-Za-z]:\//', $normalized)
                || in_array('..', explode('/', $normalized), true)) {
                throw new RuntimeException('The archive contains a forbidden path.');
            }

            $canonical = implode('/', array_values(array_filter(
                explode('/', trim($normalized, '/')),
                static fn (string $part): bool => $part !== '' && $part !== '.'
            )));
            if ($canonical === '' || isset($paths[$canonical])) {
                throw new RuntimeException('The archive contains a duplicate path.');
            }
            $paths[$canonical] = true;

            $stat = $zip->statIndex($index);
            $compressed += (int) ($stat['comp_size'] ?? 0);
            $uncompressed += (int) ($stat['size'] ?? 0);
            if ($uncompressed > 500 * 1024 * 1024 || $compressed > 100 * 1024 * 1024) {
                throw new RuntimeException('The archive is too large.');
            }

            $opsys = 0;
            $attributes = 0;
            if ($zip->getExternalAttributesIndex($index, $opsys, $attributes)
                && $opsys === 3
                && (($attributes >> 16) & 0xF000) === 0xA000) {
                throw new RuntimeException('The archive contains a symlink.');
            }
        }

        if ($compressed > 0 && $uncompressed / $compressed > 1000) {
            throw new RuntimeException('The archive compression ratio is unsafe.');
        }
    }

    public function validateConfigPaths(array $config): void
    {
        $pathKeys = ['path', 'directories', 'files', 'root', 'destination'];
        $validate = function (mixed $value, ?string $key) use (&$validate, $pathKeys): void {
            if (is_array($value)) {
                foreach ($value as $childKey => $childValue) {
                    $validate($childValue, is_string($childKey) ? $childKey : $key);
                }
                return;
            }

            if (!in_array($key, $pathKeys, true) || !is_string($value) || $value === '') {
                return;
            }

            $normalized = str_replace('\\', '/', $value);
            if (str_contains($normalized, "\0")
                || str_starts_with($normalized, '/')
                || preg_match('/^[A-Za-z]:\//', $normalized)
                || in_array('..', explode('/', $normalized), true)) {
                throw new RuntimeException('The archive manifest contains a forbidden path.');
            }
        };

        $validate($config, null);
    }
}
