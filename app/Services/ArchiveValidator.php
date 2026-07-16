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
        $paths = [];
        $paths[] = $config['path'] ?? null;
        foreach (['remove', 'create'] as $section) {
            foreach (['directories', 'files'] as $key) {
                $paths = array_merge($paths, (array) data_get($config, $section . '.' . $key, []));
            }
        }
        foreach (['directories', 'files'] as $key) {
            $paths = array_merge($paths, (array) data_get($config, 'copy.' . $key, []));
        }

        foreach ($paths as $path) {
            if (!is_string($path) || $path === '') {
                continue;
            }
            $normalized = str_replace('\\', '/', $path);
            if (str_starts_with($normalized, '/')
                || preg_match('/^[A-Za-z]:\//', $normalized)
                || in_array('..', explode('/', $normalized), true)
                || str_contains($normalized, "\0")) {
                throw new RuntimeException('The archive manifest contains a forbidden path.');
            }
        }
    }
}
