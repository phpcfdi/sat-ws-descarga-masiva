<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Utils;

use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use PhpCfdi\SatWsDescargaMasiva\Utils\CfdiPackageReader;

class CfdiPackageReaderTest extends TestCase
{
    public function testReaderZipWhenTheContentIsInvalid(): void
    {
        $zipContents = 'INVALID_ZIP_CONTENT';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Count not open zip');
        new CfdiPackageReader($zipContents);
    }

    public function testReaderZipWithOtherFiles(): void
    {
        $expectedNumberCfdis = 1;

        $zipContents = $this->fileContents('zip/cfdi_with_other_file.zip');
        $cfdiPackageReader = new CfdiPackageReader($zipContents);

        $countCfdis = count(iterator_to_array($cfdiPackageReader->cfdis(), false));

        $this->assertEquals($expectedNumberCfdis, $countCfdis);
    }

    public function testReaderCfdiInZip(): void
    {
        $expectedCfdi = $this->fileContents('zip/cfdi.xml');

        $zipContents = $this->fileContents('zip/cfdi.zip');
        $cfdiPackageReader = new CfdiPackageReader($zipContents);

        $cfdi = $cfdiPackageReader->cfdis()->current();

        $this->assertEquals($expectedCfdi, $cfdi);
    }
}
