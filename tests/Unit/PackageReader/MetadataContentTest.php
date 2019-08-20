<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataContent;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class MetadataContentTest extends TestCase
{
    public function testReadMetadata(): void
    {
        $contents = $this->fileContents('zip/metadata.txt');
        $reader = MetadataContent::createFromContents($contents);
        $extracted = [];
        foreach ($reader->eachItem() as $item) {
            $extracted[] = $item->uuid;
        }

        $expected = [
            'E7215E3B-2DC5-4A40-AB10-C902FF9258DF',
            '129C4D12-1415-4ACE-BE12-34E71C4EAB4E',
        ];
        $this->assertSame($expected, $extracted);
    }

    public function testCreateMetadataWithLessValuesThanHeaders(): void
    {
        $headers = ['foo', 'bar'];
        $values = ['x-foo'];
        $expected = ['foo' => 'x-foo', 'bar' => ''];
        $reader = MetadataContent::createFromContents('');
        $metadata = $reader->createMetadataItem($headers, $values);
        $this->assertSame($expected, $metadata->all());
    }

    public function testCreateMetadataWithMoreValuesThanHeaders(): void
    {
        $headers = ['xee', 'foo'];
        $values = ['x-xee', 'x-foo', 'x-bar'];
        $expected = ['xee' => 'x-xee', 'foo' => 'x-foo', '#extra-01' => 'x-bar'];
        $reader = MetadataContent::createFromContents('');
        $metadata = $reader->createMetadataItem($headers, $values);
        $this->assertSame($expected, $metadata->all());
    }
}
