<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests;

use DOMDocument;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Helpers\FielData;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function filePath(string $filename): string
    {
        return __DIR__ . '/_files/' . $filename;
    }

    public static function fileContents(string $filename): string
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        return strval(@file_get_contents(static::filePath($filename))) ?: '';
    }

    public function createFielRequestBuilderUsingTestingFiles(string $password = null): FielRequestBuilder
    {
        $fiel = $this->createFielUsingTestingFiles($password);
        return new FielRequestBuilder($fiel);
    }

    public function createFielUsingTestingFiles(string $password = null): Fiel
    {
        $fielData = new FielData(
            $this->filePath('fake-fiel/EKU9003173C9.cer'),
            $this->filePath('fake-fiel/EKU9003173C9.key'),
            $password ?? trim($this->fileContents('fake-fiel/EKU9003173C9-password.txt'))
        );
        return $fielData->createFiel();
    }

    public static function xmlFormat(string $content): string
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        $document->loadXML($content);
        return $document->saveXML() ?: '';
    }
}
