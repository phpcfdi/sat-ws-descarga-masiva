<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal;

use Generator;
use LogicException;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\ThirdPartiesFileFilter;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\PackageReaderInterface;

/**
 * Class to extract the data from a "third parties" file.
 *
 * @internal
 */
final class ThirdPartiesExtractor
{
    public function __construct(private readonly CsvReader $csvReader)
    {
    }

    public static function createFromPackageReader(PackageReaderInterface $packageReader): self
    {
        if (! $packageReader instanceof FilteredPackageReader) {
            throw new LogicException('PackageReader parameter must be a FilteredPackageReader');
        }

        $previousFilter = $packageReader->changeFilter(new ThirdPartiesFileFilter());
        $contents = '';
        foreach ($packageReader->fileContents() as $fileContents) {
            $contents = $fileContents;
            break;
        }
        $packageReader->setFilter($previousFilter);

        return new self(CsvReader::createFromContents($contents));
    }

    /**
     * The generator return the UUID as key and an array with two key/values: rfcACuentaTerceros & nombreACuentaTerceros
     *
     * @return Generator<string, array{RfcACuentaTerceros: string, NombreACuentaTerceros: string}>
     */
    public function eachRecord(): Generator
    {
        foreach ($this->csvReader->records() as $data) {
            $uuid = strtoupper(strval($data['Uuid'] ?? ''));
            if ('' === $uuid) {
                continue;
            }

            yield $uuid => [
                'RfcACuentaTerceros' => strval($data['RfcACuentaTerceros'] ?? ''),
                'NombreACuentaTerceros' => strval($data['NombreACuentaTerceros'] ?? ''),
            ];
        }
    }
}
