<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests;

use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;

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

    public function createFielUsingTestingFiles(string $password = null): Fiel
    {
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL_password.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            $password ?? trim($this->fileContents('fake-fiel/password.txt'))
        );
        return $fiel;
    }
}
