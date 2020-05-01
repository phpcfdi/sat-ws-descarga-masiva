<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\CfdiPackageReader;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use RuntimeException;

class CfdiPackageReaderTest extends TestCase
{
    public function testReaderZipWhenTheContentIsInvalid(): void
    {
        $zipContents = 'INVALID_ZIP_CONTENT';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not open zip');
        CfdiPackageReader::createFromContents($zipContents);
    }

    public function testReaderZipWhenTheContentValid(): void
    {
        $zipContents = $this->fileContents('zip/cfdi.zip');
        $cfdiPackageReader = CfdiPackageReader::createFromContents($zipContents);
        $temporaryFilename = $cfdiPackageReader->getFilename();
        unset($cfdiPackageReader);
        $this->assertFileDoesNotExist(
            $temporaryFilename,
            'When creating a CfdiPackageReader from contents, once it is destroyed relative file must not exists'
        );
    }

    public function testReaderZipWithOtherFiles(): void
    {
        $expectedNumberCfdis = 1;

        $filename = $this->filePath('zip/cfdi.zip');
        $cfdiPackageReader = new CfdiPackageReader($filename);

        $this->assertCount($expectedNumberCfdis, $cfdiPackageReader);
    }

    public function testReaderCfdiInZip(): void
    {
        $expectedCfdi = $this->fileContents('zip/cfdi.xml');

        $zipFilename = $this->filePath('zip/cfdi.zip');
        $cfdiPackageReader = new CfdiPackageReader($zipFilename);

        $cfdi = $cfdiPackageReader->fileContents()->current();
        $this->assertEquals($expectedCfdi, $cfdi);
    }
}
