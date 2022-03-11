<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Integration;

use PhpCfdi\SatWsDescargaMasiva\Service;

final class ConsumeCfdiServicesUsingFakeFielTest extends ConsumeServiceTestCase
{
    protected function createService(): Service
    {
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();
        $webClient = $this->createWebClient();
        return new Service($requestBuilder, $webClient);
    }
}
