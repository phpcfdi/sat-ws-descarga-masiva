<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataPackageReader;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

/**
 * This tests uses the Zip file located at tests/_files/zip/metadata.zip that contains:
 *
 * __MACOSX/ // commonly generated by MacOS when open the file
 * __MACOSX/._45C5C344-DA01-497A-9271-5AA3852EE6AE_01.txt // commonly generated by MacOS when open the file
 * 00000000-0000-0000-0000-000000000000_00.txt // file with correct name but not a metadata file
 * 45C5C344-DA01-497A-9271-5AA3852EE6AE_01.txt // file with metadata 2 rows
 * empty-file // zero bytes file
 * other.txt // file with incorrect extension and incorrect content
 */
class MetadataPackageReaderTest extends TestCase
{
    public function testCountAllContents(): void
    {
        $expectedNumberFiles = 1;
        $expectedNumberRows = 2;

        $filename = $this->filePath('zip/metadata.zip');
        $metadataPackageReader = MetadataPackageReader::createFromFile($filename);

        $this->assertCount($expectedNumberRows, $metadataPackageReader);
        $this->assertCount($expectedNumberFiles, $metadataPackageReader->fileContents());
    }

    public function testRetrieveMetadataContents(): void
    {
        $filename = $this->filePath('zip/metadata.zip');
        $metadataPackageReader = MetadataPackageReader::createFromFile($filename);

        $this->assertCount(2, $metadataPackageReader->metadata());

        $extracted = [];
        foreach ($metadataPackageReader->metadata() as $item) {
            $extracted[] = $item->uuid;
        }

        $expected = [
            'E7215E3B-2DC5-4A40-AB10-C902FF9258DF',
            '129C4D12-1415-4ACE-BE12-34E71C4EAB4E',
        ];
        $this->assertSame($expected, $extracted);
    }

    public function testCreateFromFileAndContents(): void
    {
        $filename = $this->filePath('zip/metadata.zip');
        $first = MetadataPackageReader::createFromFile($filename);
        $this->assertSame($filename, $first->getFilename());

        $contents = $this->fileContents('zip/metadata.zip');
        $second = MetadataPackageReader::createFromContents($contents);

        $this->assertEquals(
            iterator_to_array($first->metadata()),
            iterator_to_array($second->metadata()),
            'createFromFile & createFromContents get the same contents'
        );
    }

    public function testJson(): void
    {
        $zipFilename = $this->filePath('zip/metadata.zip');
        $packageReader = MetadataPackageReader::createFromFile($zipFilename);
        $this->assertInstanceOf(JsonSerializable::class, $packageReader);

        /** @var array<string, string|string[]> $jsonData */
        $jsonData = $packageReader->jsonSerialize();

        $this->assertSame($zipFilename, $jsonData['source'] ?? '');

        $expectedFiles = [
            '45C5C344-DA01-497A-9271-5AA3852EE6AE_01.txt',
        ];
        /** @var string[] $jsonDataFiles */
        $jsonDataFiles = $jsonData['files'];
        $this->assertSame($expectedFiles, array_keys($jsonDataFiles));

        $expectedMetadata = [
            'E7215E3B-2DC5-4A40-AB10-C902FF9258DF',
            '129C4D12-1415-4ACE-BE12-34E71C4EAB4E',
        ];
        /** @var string[] $jsonDataMetadata */
        $jsonDataMetadata = $jsonData['metadata'];
        $this->assertSame($expectedMetadata, array_keys($jsonDataMetadata));
    }

    public function testMetadataJson(): void
    {
        $zipFilename = $this->filePath('zip/metadata.zip');
        $packageReader = MetadataPackageReader::createFromFile($zipFilename);

        $expectedFile = $this->filePath('zip/metadata.json');
        $metadata = iterator_to_array($packageReader->metadata());
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($metadata) ?: '');
    }
}
