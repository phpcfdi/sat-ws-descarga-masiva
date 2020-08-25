<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Internal;

use PhpCfdi\SatWsDescargaMasiva\Internal\SoapFaultInfoExtractor;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\WebClient\SoapFaultInfo;

class SoapFaultInfoExtractorTest extends TestCase
{
    public function testExtractOnFaultyResponse(): void
    {
        $source = $this->fileContents('authenticate/response-with-error.xml');
        $fault = SoapFaultInfoExtractor::extract($source);
        if (null === $fault) {
            $this->fail('It was expected to receive an instace of SoapFaultInfo');
            return;
        }
        $this->assertInstanceOf(SoapFaultInfo::class, $fault);
        $this->assertSame('a:InvalidSecurity', $fault->getCode());
        $this->assertSame('An error occurred when verifying security for the message.', $fault->getMessage());
    }

    public function testExtractOnNotFaultyResponse(): void
    {
        $source = $this->fileContents('authenticate/response-with-token.xml');
        $fault = SoapFaultInfoExtractor::extract($source);
        $this->assertNull($fault);
    }
}
