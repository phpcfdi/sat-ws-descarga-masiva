<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Verify;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyResult;
use PhpCfdi\SatWsDescargaMasiva\Shared\CodeRequest;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusRequest;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class VerifyResultTest extends TestCase
{
    public function testProperties(): void
    {
        $statusCode = new StatusCode(5000, 'Solicitud recibida con éxito');
        $statusRequest = new StatusRequest(3);
        $codeRequest = new CodeRequest(5003);
        $packagesIds = ['x-package-1', 'x-package-2'];
        $numberCfdis = 1000;
        $result = new VerifyResult($statusCode, $statusRequest, $codeRequest, $numberCfdis, ...$packagesIds);
        $this->assertSame($statusCode, $result->getStatus());
        $this->assertSame($statusRequest, $result->getStatusRequest());
        $this->assertSame($codeRequest, $result->getCodeRequest());
        $this->assertSame($numberCfdis, $result->getNumberCfdis());
        $this->assertSame($packagesIds, $result->getPackagesIds());
    }

    public function testJson(): void
    {
        $statusCode = new StatusCode(5000, 'Solicitud recibida con éxito');
        $statusRequest = new StatusRequest(3);
        $codeRequest = new CodeRequest(5003);
        $packagesIds = ['x-package-1', 'x-package-2'];
        $numberCfdis = 1000;
        $result = new VerifyResult($statusCode, $statusRequest, $codeRequest, $numberCfdis, ...$packagesIds);
        $this->assertInstanceOf(JsonSerializable::class, $result);
        $expectedFile = $this->filePath('json/verify-result.json');
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($result) ?: '');
    }
}
