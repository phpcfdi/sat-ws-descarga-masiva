<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\RequestBuilder\FielRequestBuilder;

use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class FielRequestBuilderTest extends TestCase
{
    public function testFielRequestImplementsRequestBuilderInterface(): void
    {
        $expected = RequestBuilderInterface::class;
        $interfaces = class_implements(FielRequestBuilder::class);
        $this->assertContains($expected, $interfaces);
    }

    public function testFielRequestContainsGivenFiel(): void
    {
        $fiel = Fiel::create(
            $this->fileContents('fake-fiel/EKU9003173C9.cer'),
            $this->fileContents('fake-fiel/EKU9003173C9.key'),
            trim($this->fileContents('fake-fiel/EKU9003173C9-password.txt'))
        );
        $requestBuilder = new FielRequestBuilder($fiel);
        $this->assertSame($fiel, $requestBuilder->getFiel());
    }
}
