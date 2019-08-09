<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataPackageReader;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class MetadataPackageReaderTest extends TestCase
{
    public function testRetrieveAllContents(): void
    {
        $expectedNumberCfdis = 1;

        $filename = $this->filePath('zip/metadata.zip');
        $metadataPackageReader = new MetadataPackageReader($filename);

        $this->assertCount($expectedNumberCfdis, $metadataPackageReader);
    }

    public function testRetrieveMetadata(): void
    {
        $filename = $this->filePath('zip/metadata.zip');
        $metadataPackageReader = new MetadataPackageReader($filename);

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
}
