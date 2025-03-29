<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\WebClient;

use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\SoapFaultInfo;

class SoapFaultInfoTest extends TestCase
{
    public function testDataTransferObject(): void
    {
        $code = 'x-code';
        $message = 'x-message';
        $fault = new SoapFaultInfo($code, $message);
        $this->assertSame($code, $fault->getCode());
        $this->assertSame($message, $fault->getMessage());
        $this->assertSame($message, (string) $fault);
        $this->assertSame(['code' => $code, 'message' => $message], json_decode(json_encode($fault) ?: '', true));
    }
}
