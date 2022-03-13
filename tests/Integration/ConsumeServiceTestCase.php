<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;

abstract class ConsumeServiceTestCase extends TestCase
{
    abstract protected function createService(): Service;

    protected function createWebClient(): WebClientInterface
    {
        $guzzleClient = new GuzzleClient([
            RequestOptions::CONNECT_TIMEOUT => 5,
            RequestOptions::TIMEOUT => 30,
        ]);
        return new GuzzleWebClient($guzzleClient);
    }

    public function testAuthentication(): void
    {
        $service = $this->createService();
        $token = $service->authenticate();
        $this->assertTrue($token->isValid());
    }

    public function testQueryIssued(): void
    {
        $service = $this->createService();

        $dateTimePeriod = DateTimePeriod::create(DateTime::create('2019-01-01 00:00:00'), DateTime::create('2019-01-01 00:04:00'));
        $parameters = QueryParameters::create($dateTimePeriod, DownloadType::issued(), RequestType::cfdi());

        $result = $service->query($parameters);
        $this->assertSame(
            305,
            $result->getStatus()->getCode(),
            'Expected to receive a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }

    public function testQueryReceived(): void
    {
        $service = $this->createService();

        $dateTimePeriod = DateTimePeriod::create(DateTime::create('2019-01-01 00:00:00'), DateTime::create('2019-01-01 00:04:00'));
        $parameters = QueryParameters::create($dateTimePeriod, DownloadType::received(), RequestType::cfdi());

        $result = $service->query($parameters);
        $this->assertSame(
            305,
            $result->getStatus()->getCode(),
            'Expected to receive a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }

    public function testVerify(): void
    {
        $service = $this->createService();

        $requestId = '3edbd462-9fa0-4363-b60f-bac332338028';
        $result = $service->verify($requestId);
        $this->assertSame(
            305,
            $result->getStatus()->getCode(),
            'Expected to receive a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }

    public function testDownload(): void
    {
        $service = $this->createService();

        $requestId = '4e80345d-917f-40bb-a98f-4a73939343c5_01';
        $result = $service->download($requestId);
        $this->assertSame(
            305,
            $result->getStatus()->getCode(),
            'Expected to receive a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }
}
