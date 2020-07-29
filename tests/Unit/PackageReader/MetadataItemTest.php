<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataItem;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataPackageReader;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class MetadataItemTest extends TestCase
{
    public function testWithEmptyData(): void
    {
        $metadata = new MetadataItem([]);
        $this->assertSame('', $metadata->uuid);
        $this->assertSame('', $metadata->get('uuid'));
        $this->assertSame([], $metadata->all());
    }

    public function testWithContents(): void
    {
        $data = ['uuid' => 'x-uuid', 'oneData' => 'one data'];
        $metadata = new MetadataItem($data);
        $this->assertSame('x-uuid', $metadata->uuid);
        $this->assertSame('x-uuid', $metadata->get('uuid'));
        $this->assertSame('one data', $metadata->get('oneData'));
        $this->assertSame('one data', $metadata->{'oneData'});
        $this->assertSame($data, $metadata->all());
    }

    public function testReaderCfdiInZip(): void
    {
        $expectedContent = $this->fileContents('zip/metadata.txt');

        $zipFilename = $this->filePath('zip/metadata.zip');
        $cfdiPackageReader = MetadataPackageReader::createFromFile($zipFilename);
        $extracted = current(iterator_to_array($cfdiPackageReader->fileContents()));

        // normalize line endings
        $expectedContent = str_replace("\r\n", "\n", $expectedContent);
        $extracted = str_replace("\r\n", "\n", $extracted);
        $this->assertSame($expectedContent, $extracted);
    }
}
