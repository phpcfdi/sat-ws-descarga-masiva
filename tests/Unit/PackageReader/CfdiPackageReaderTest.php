<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\CfdiPackageReader;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions\OpenZipFileException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

/**
 * This tests uses the Zip file located at tests/_files/zip/cfdi.zip that contains:
 *
 * __MACOSX/ // commonly generated by MacOS when open the file
 * __MACOSX/.aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml // commonly generated by MacOS when open the file
 * aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml // valid cfdi with common name
 * aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml.xml // valid cfdi with double extension (oh my SAT!)
 * 00000000-0000-0000-0000-000000000000.xml // file with correct name but not a cfdi
 * empty-file // zero bytes file
 * other.txt // file with incorrect extension and incorrect content
 *
 */
class CfdiPackageReaderTest extends TestCase
{
    public function testReaderZipWhenTheContentIsInvalid(): void
    {
        $zipContents = 'INVALID_ZIP_CONTENT';
        $this->expectException(OpenZipFileException::class);
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
        $expectedNumberCfdis = 2;

        $filename = $this->filePath('zip/cfdi.zip');
        $cfdiPackageReader = CfdiPackageReader::createFromFile($filename);

        $this->assertCount($expectedNumberCfdis, $cfdiPackageReader);
    }

    public function testReaderZipWithOtherFilesAndDoubleXmlExtension(): void
    {
        // there are 2 valid files:
        // "aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml" and
        // "aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml.xml"
        $expectedFilenames = [
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml',
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml.xml',
        ];
        sort($expectedFilenames);

        $filename = $this->filePath('zip/cfdi.zip');
        $cfdiPackageReader = CfdiPackageReader::createFromFile($filename);

        $filenames = array_keys(iterator_to_array($cfdiPackageReader->fileContents()));
        sort($filenames);
        $this->assertEquals($expectedFilenames, $filenames);
    }

    public function testCfdiReaderObtainFirstFileAsExpected(): void
    {
        $expectedCfdi = $this->fileContents('zip/cfdi.xml');

        $zipFilename = $this->filePath('zip/cfdi.zip');
        $cfdiPackageReader = CfdiPackageReader::createFromFile($zipFilename);

        $cfdi = current(iterator_to_array($cfdiPackageReader->fileContents()));
        $this->assertSame($expectedCfdi, $cfdi);
    }

    public function testCreateFromFileAndContents(): void
    {
        $filename = $this->filePath('zip/cfdi.zip');
        $first = CfdiPackageReader::createFromFile($filename);
        $this->assertSame($filename, $first->getFilename());

        $contents = $this->fileContents('zip/cfdi.zip');
        $second = CfdiPackageReader::createFromContents($contents);

        $this->assertEquals(
            iterator_to_array($first->cfdis()),
            iterator_to_array($second->cfdis()),
            'createFromFile & createFromContents get the same contents'
        );
    }

    /** @return array<string, array<string>> */
    public function providerObtainUuidFromXmlCfdi(): array
    {
        return [
            'common' => [<<<XML
                <cfdi:Complemento>
                    <tfd:TimbreFiscalDigital UUID="ff833b27-c8ab-4c44-a559-2c197bdd4067"/>
                <cfdi:Complemento/>
                XML,
                'ff833b27-c8ab-4c44-a559-2c197bdd4067',
            ],
            'upper-case' => [<<<XML
                <cfdi:Complemento>
                    <tfd:TimbreFiscalDigital UUID="FF833B27-C8AB-4C44-A559-2C197BDD4067"/>
                <cfdi:Complemento/>
                XML,
                'ff833b27-c8ab-4c44-a559-2c197bdd4067',
            ],
            'middle-vertical-content' => [<<<XML
                <cfdi:Complemento>
                    <tfd:TimbreFiscalDigital a="a" UUID="ff833b27-c8ab-4c44-a559-2c197bdd4067" b="b"/>
                <cfdi:Complemento/>
                XML,
                'ff833b27-c8ab-4c44-a559-2c197bdd4067',
            ],
            'middle-vertical-space' => [<<<XML
                <cfdi:Complemento>
                    <tfd:TimbreFiscalDigital
                        UUID="ff833b27-c8ab-4c44-a559-2c197bdd4067"
                    />
                <cfdi:Complemento/>
                XML,
                'ff833b27-c8ab-4c44-a559-2c197bdd4067',
            ],
            'invalid-uuid' => [<<<XML
                <cfdi:Complemento>
                    <tfd:TimbreFiscalDigital
                        UUID="ff833b27-ÑÑÑÑ-4c44-a559-2c197bdd4067"
                    />
                <cfdi:Complemento/>
                XML,
                '',
            ],
            'empty-content' => ['', ''],
            'invalid xml' => ['invalid xml', ''],
            'xml without tfd' => ['<xml/>', ''],
            'with cfdi relacionado and xmlns:tfd' => [<<<XML
                <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3"
                  xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital">
                  <cfdi:CfdiRelacionados TipoRelacion="07">
                    <cfdi:CfdiRelacionado UUID="afbef86d-d7af-4ef4-acfb-6aef8ddfa313"/>
                  </cfdi:CfdiRelacionados>
                  <cfdi:Complemento>
                    <tfd:TimbreFiscalDigital UUID="000d04ba-18b8-4b78-b266-7fa7bdb24603"/>
                  </cfdi:Complemento>
                </cfdi:Comprobante>
                XML,
                '000d04ba-18b8-4b78-b266-7fa7bdb24603',
            ],
        ];
    }

    /**
     * @param string $source
     * @param string $expected
     * @dataProvider providerObtainUuidFromXmlCfdi
     */
    public function testObtainUuidFromXmlCfdi(string $source, string $expected): void
    {
        $uuid = CfdiPackageReader::obtainUuidFromXmlCfdi($source);
        $this->assertSame($expected, $uuid);
    }

    public function testJson(): void
    {
        $zipFilename = $this->filePath('zip/cfdi.zip');
        $packageReader = CfdiPackageReader::createFromFile($zipFilename);
        $this->assertInstanceOf(JsonSerializable::class, $packageReader);

        /** @var array<string, string|string[]> $jsonData */
        $jsonData = $packageReader->jsonSerialize();

        $this->assertSame($zipFilename, $jsonData['source'] ?? '');

        $expectedFiles = [
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml',
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.xml.xml',
        ];
        /** @var string[] $jsonDataFiles */
        $jsonDataFiles = $jsonData['files'];
        $this->assertSame($expectedFiles, array_keys($jsonDataFiles));

        $expectedCfdis = [
            '11111111-2222-3333-4444-000000000001',
        ];
        /** @var string[] $jsonDataCfdis */
        $jsonDataCfdis = $jsonData['cfdis'];
        $this->assertSame($expectedCfdis, array_keys($jsonDataCfdis));
    }
}
