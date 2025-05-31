<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit;

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\Shared\Token;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;

final class ServiceTest extends TestCase
{
    public function testCreateServiceWithMinimalValues(): void
    {
        $requestBuilder = $this->createMock(RequestBuilderInterface::class);
        $webClient = $this->createMock(WebClientInterface::class);
        $service = new Service($requestBuilder, $webClient);

        // test token when not set
        $this->assertFalse($service->getToken()->isValid());
        $this->assertTrue($service->getToken()->isValueEmpty());

        // test endpoints when not set
        $this->assertTrue($service->getEndpoints()->getServiceType()->isCfdi());
    }

    public function testCreateServiceWithAllParameters(): void
    {
        $requestBuilder = $this->createMock(RequestBuilderInterface::class);
        $webClient = $this->createMock(WebClientInterface::class);
        $token = new Token(DateTime::now(), DateTime::now(), 'token-value');
        $endpoints = ServiceEndpoints::retenciones();
        $service = new Service($requestBuilder, $webClient, $token, $endpoints);

        $this->assertSame($token, $service->getToken());
        $this->assertSame($endpoints, $service->getEndpoints());
    }

    public function testChangeToken(): void
    {
        $requestBuilder = $this->createMock(RequestBuilderInterface::class);
        $webClient = $this->createMock(WebClientInterface::class);
        $token = new Token(DateTime::now(), DateTime::now(), 'token-value');
        $endpoints = ServiceEndpoints::retenciones();

        $service = new Service($requestBuilder, $webClient, $token, $endpoints);
        $otherToken = new Token(DateTime::now(), DateTime::now(), 'token-other');
        $service->setToken($otherToken);

        $this->assertSame($otherToken, $service->getToken());
    }
}
