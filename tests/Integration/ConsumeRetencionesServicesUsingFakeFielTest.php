<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;

final class ConsumeRetencionesServicesUsingFakeFielTest extends ConsumeServiceTestCase
{
    protected function createService(): Service
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $webclient = new GuzzleWebClient();
        return new Service($requestBuilder, $webclient, null, ServiceEndpoints::retenciones());
    }
}
