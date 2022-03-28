<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Download;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadResult;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class DownloadResultTest extends TestCase
{
    public function testProperties(): void
    {
        $statusCode = new StatusCode(5000, 'Solicitud recibida con éxito');
        $packageContent = 'x-content';
        $packageSize = strlen($packageContent);
        $result = new DownloadResult($statusCode, $packageContent);
        $this->assertSame($statusCode, $result->getStatus());
        $this->assertSame($packageContent, $result->getPackageContent());
        $this->assertSame($packageSize, $result->getPackageSize());
    }

    /** @noinspection PhpDeprecationInspection */
    public function testGetPackageLengthIsDeprecated(): void
    {
        $statusCode = new StatusCode(5000, 'Solicitud recibida con éxito');
        $packageContent = 'x-content';
        $packageSize = strlen($packageContent);
        $result = new DownloadResult($statusCode, $packageContent);

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $this->assertSame($packageSize, @$result->getPackageLenght());

        $this->expectDeprecation();
        $this->expectDeprecationMessage('Method DownloadResult::getPackageLenght() is deprecated');
        $result->getPackageLenght();
    }

    public function testJson(): void
    {
        $statusCode = new StatusCode(5000, 'Solicitud recibida con éxito');
        $packageContent = 'x-content';
        $result = new DownloadResult($statusCode, $packageContent);
        $this->assertInstanceOf(JsonSerializable::class, $result);
        $expectedFile = $this->filePath('json/download-result.json');
        $this->assertSame(
            ['status', 'size'],
            array_keys($result->jsonSerialize()),
            'jsonSerialize must not include content, only status and size'
        );
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($result) ?: '');
    }
}
