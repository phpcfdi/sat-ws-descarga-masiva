<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader\Internal;

use ArrayIterator;
use LogicException;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\CsvReader;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\NullFileFilter;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FilteredPackageReader;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\ThirdPartiesExtractor;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\PackageReaderInterface;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class ThirdPartiesExtractorTest extends TestCase
{
    public function testExtractor(): void
    {
        $source = [
            ['Uuid', 'RfcACuentaTerceros', 'NombreACuentaTerceros'],
            ['00000000-aaaa-bbbb-1111-000000000001', 'AAAA010101AAA', 'Registro de ejemplo 1'],
            ['00000000-aaaa-bbbb-1111-000000000002', 'AAAA010102AAA', 'Registro de ejemplo 2'],
            ['00000000-aaaa-bbbb-1111-000000000003', 'AAAA010103AAA', 'Registro de ejemplo 3'],
        ];
        $expected = [
            '00000000-AAAA-BBBB-1111-000000000001' => [
                'RfcACuentaTerceros' => 'AAAA010101AAA',
                'NombreACuentaTerceros' => 'Registro de ejemplo 1',
            ],
            '00000000-AAAA-BBBB-1111-000000000002' => [
                'RfcACuentaTerceros' => 'AAAA010102AAA',
                'NombreACuentaTerceros' => 'Registro de ejemplo 2',
            ],
            '00000000-AAAA-BBBB-1111-000000000003' => [
                'RfcACuentaTerceros' => 'AAAA010103AAA',
                'NombreACuentaTerceros' => 'Registro de ejemplo 3',
            ],
        ];
        $extractor = new ThirdPartiesExtractor(new CsvReader(new ArrayIterator($source)));
        $this->assertSame($expected, iterator_to_array($extractor->eachRecord()));
    }

    public function testEmptyUuidIsIgnored(): void
    {
        $source = [
            ['Uuid', 'RfcACuentaTerceros', 'NombreACuentaTerceros'],
            ['', 'AAAA010101AAA', 'Registro de ejemplo 1'],
        ];
        $expected = [];
        $extractor = new ThirdPartiesExtractor(new CsvReader(new ArrayIterator($source)));
        $this->assertSame($expected, iterator_to_array($extractor->eachRecord()));
    }

    public function testCreateFromPackageReaderNotFilteredPackageReader(): void
    {
        $packageReader = $this->createMock(PackageReaderInterface::class);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PackageReader parameter must be a FilteredPackageReader');
        ThirdPartiesExtractor::createFromPackageReader($packageReader);
    }

    public function testCreateFromPackageReaderRestoreFilter(): void
    {
        $packageReader = FilteredPackageReader::createFromFile($this->filePath('zip/metadata.zip'));
        $filter = new NullFileFilter();
        $packageReader->setFilter($filter);

        ThirdPartiesExtractor::createFromPackageReader($packageReader);

        $this->assertSame(
            $filter,
            $packageReader->getFilter(),
            'FilteredPackageReader filter must not change after call createFromPackageReader'
        );
    }
}
