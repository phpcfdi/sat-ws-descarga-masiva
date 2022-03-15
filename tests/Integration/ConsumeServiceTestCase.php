<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\CfdiComplemento;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;
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

    public function testQueryDefaultParameters(): void
    {
        $service = $this->createService();

        $parameters = QueryParameters::create();

        $result = $service->query($parameters);
        $this->assertSame(
            305,
            $result->getStatus()->getCode(),
            'Expected to receive a 305 - Certificado Inválido from SAT since FIEL is for testing'
        );
    }

    public function testQueryChangeAllParameters(): void
    {
        $service = $this->createService();

        $parameters = QueryParameters::create()
            ->withPeriod(DateTimePeriod::createFromValues('2019-01-01 00:00:00', '2019-01-01 00:04:00'))
            ->withDownloadType(DownloadType::received())
            ->withRequestType(RequestType::cfdi())
            ->withDocumentType(DocumentType::nomina())
            /** @todo uncomment this line when SAT service is working */
            // ->withComplement(CfdiComplemento::leyendasFiscales10())
            ->withDocumentStatus(DocumentStatus::active())
            ->withUuid(Uuid::create('96623061-61fe-49de-b298-c7156476aa8b'))
            ->withRfcOnBehalf(RfcOnBehalf::create('XXX01010199A'))
            ->withRfcMatch(RfcMatch::create('AAA010101AAA'))
        ;

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
