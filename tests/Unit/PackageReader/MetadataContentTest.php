<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\MetadataContent;
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

    public function testReadMetadataWithBlankLines(): void
    {
        $contents = implode(PHP_EOL, [
            '', // leading blank line
            'id~text',
            '', // before data blank line
            '1~one',
            '2~two',
            '', // inner data blank line
            '3~three',
            '', // trailing blank lines
            '',
        ]);
        $reader = MetadataContent::createFromContents($contents);
        $extracted = [];
        foreach ($reader->eachItem() as $item) {
            $extracted[] = ['id' => $item->get('id'), 'text' => $item->get('text')];
        }

        $expected = [
            ['id' => '1', 'text' => 'one'],
            ['id' => '2', 'text' => 'two'],
            ['id' => '3', 'text' => 'three'],
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

    /** @return array<string, array{string, string}> */
    public function providerReadMetadataWithSpecialCharacters(): array
    {
        return [
            'simple' => ['Receptor SA', 'Receptor SA'],
            'quotes on complete field' => ['"Receptor SA"', '"Receptor SA"'],
            'quotes on first word' => ['"Receptor" SA', '"Receptor" SA'],
            'quotes on last word' => ['Receptor "SA"', 'Receptor "SA"'],
            'quotes on middle word' => ['Receptor "Foo" SA', 'Receptor "Foo" SA'],
            'quote in the middle' => ['Receptor " SA', 'Receptor " SA'],
            'LF after first quote' => ["\"\nReceptor SA\"", '"Receptor SA"'],
            'LF before last quote' => ["\"Receptor SA\n\"", '"Receptor SA"'],
            'LF between quotes' => ["\"Receptor\nSA\"", '"ReceptorSA"'],
            'LFLF between quotes' => ["\"Receptor\n\nSA\"", '"ReceptorSA"'],
            'LF at end' => ["Receptor SA\n", 'Receptor SA'],
            'LF at start' => ["\nReceptor SA", 'Receptor SA'],
            'LF in the middle' => ["Receptor\nSA", 'ReceptorSA'],
            'LFLF at start' => ["Receptor\n\nSA", 'ReceptorSA'],
        ];
    }

    /**
     * @param string $sourceValue
     * @param string $expectedValue
     * @dataProvider providerReadMetadataWithSpecialCharacters
     */
    public function testReadMetadataWithSpecialCharacters(string $sourceValue, string $expectedValue): void
    {
        $contents = implode("\r\n", [
            implode('~', ['id', 'value', 'foo', 'bar']),
            implode('~', ['1', $sourceValue, 'x-foo', 'x-bar']),
            implode('~', ['2', 'second', 'x-foo', 'x-bar']),
        ]);
        $reader = MetadataContent::createFromContents($contents);

        $extracted = [];
        foreach ($reader->eachItem() as $item) {
            $extracted[] = $item->get('value');
        }

        $this->assertSame($expectedValue, $extracted[0]);
        $this->assertCount(2, $extracted);
    }
}
