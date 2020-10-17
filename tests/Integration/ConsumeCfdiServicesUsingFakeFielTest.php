<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\RequestOptions;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;

final class ConsumeCfdiServicesUsingFakeFielTest extends ConsumeServiceTestCase
{
    protected function createService(): Service
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        // 2020-10-16 The server https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/ has invalid certificate
        // There is not a problem for testing but do not execute use insecure connections with production data
        // 2020-10-17 The same problem now exists only on https://cfdidescargamasiva.clouda.sat.gob.mx/
        $customGuzzleHttpClient = new GuzzleHttpClient([RequestOptions::VERIFY => false]);
        $webclient = new GuzzleWebClient($customGuzzleHttpClient);
        return new Service($requestBuilder, $webclient);
    }
}
