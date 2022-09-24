<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader\Internal;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\CsvReader;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class CsvReaderTest extends TestCase
{
    public function testReadWithBlankLines(): void
    {
        $contents = implode("\r\n", [
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
        $reader = CsvReader::createFromContents($contents);
        $extracted = [];
        foreach ($reader->records() as $item) {
            $extracted[] = ['id' => $item['id'], 'text' => $item['text']];
        }

        $expected = [
            ['id' => '1', 'text' => 'one'],
            ['id' => '2', 'text' => 'two'],
            ['id' => '3', 'text' => 'three'],
        ];
        $this->assertSame($expected, $extracted);
    }

    public function testCombineWithLessValuesThanKeys(): void
    {
        $keys = ['foo', 'bar'];
        $values = ['x-foo'];
        $expected = ['foo' => 'x-foo', 'bar' => ''];
        $reader = CsvReader::createFromContents('');
        $combined = $reader->combine($keys, $values);
        $this->assertSame($expected, $combined);
    }

    public function testCombineWithMoreValuesThanKeys(): void
    {
        $keys = ['xee', 'foo'];
        $values = ['x-xee', 'x-foo', 'x-bar'];
        $expected = ['xee' => 'x-xee', 'foo' => 'x-foo', '#extra-01' => 'x-bar'];
        $reader = CsvReader::createFromContents('');
        $combined = $reader->combine($keys, $values);
        $this->assertSame($expected, $combined);
    }
}
