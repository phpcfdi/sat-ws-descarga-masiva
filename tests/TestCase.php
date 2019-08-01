<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function filePath(string $filename): string
    {
        return __DIR__ . '/_files/' . $filename;
    }

    public static function fileContents(string $filename): string
    {
        return file_get_contents(static::filePath($filename)) ?: '';
    }
}
