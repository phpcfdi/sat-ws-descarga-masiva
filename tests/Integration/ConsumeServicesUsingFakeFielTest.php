<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\Tests\WebClient\GuzzleWebClient;

class ConsumeServicesUsingFakeFielTest extends TestCase
{
    protected function createService(): Service
    {
        $fiel = $this->createFielUsingTestingFiles();
        $webclient = new GuzzleWebClient();
        return  new Service($fiel, $webclient);
    }

    public function testAuthentication(): void
    {
        $service = $this->createService();
        $token = $service->authenticate();
        $this->assertTrue($token->isValid());
    }

    public function testQuery(): void
    {
        $service = $this->createService();

        $dateTimePeriod = new DateTimePeriod(new DateTime('2019-01-01 00:00:00'), new DateTime('2019-01-01 00:04:00'));
        $downloadRequestQuery = new QueryParameters($dateTimePeriod, DownloadType::received(), RequestType::cfdi());

        $downloadRequestResult = $service->downloadRequest($downloadRequestQuery);
        $this->assertSame(
            305,
            $downloadRequestResult->getStatusCode(),
            'Expected to recieve a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }

    public function testVerifyUsing(): void
    {
        $service = $this->createService();

        $requestId = '3edbd462-9fa0-4363-b60f-bac332338028';
        $downloadRequestResult = $service->verifyDownloadRequest($requestId);
        $this->assertSame(
            305,
            $downloadRequestResult->getStatusCode(),
            'Expected to recieve a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }
}
