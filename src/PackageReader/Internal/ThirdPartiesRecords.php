<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\PackageReaderInterface;

final class ThirdPartiesRecords
{
    /**
     * @param array<string, array{RfcACuentaTerceros: string, NombreACuentaTerceros: string}> $records
     */
    public function __construct(private array $records)
    {
    }

    public static function createEmpty(): self
    {
        return new self([]);
    }

    public static function createFromPackageReader(PackageReaderInterface $packageReader): self
    {
        $thirdPartiesBuilder = ThirdPartiesExtractor::createFromPackageReader($packageReader);
        $records = [];
        foreach ($thirdPartiesBuilder->eachRecord() as $uuid => $values) {
            $records[self::formatUuid($uuid)] = $values;
        }
        return new self($records);
    }

    private static function formatUuid(string $uuid): string
    {
        return strtolower($uuid);
    }

    /**
     * @param array<string, string> $data
     * @return array<string, string>
     */
    public function addToData(array $data): array
    {
        $uuid = $data['Uuid'] ?? '';
        $values = $this->getDataFromUuid($uuid);
        return array_merge($data, $values);
    }

    /**
     * @return array{RfcACuentaTerceros: string, NombreACuentaTerceros: string}
     */
    public function getDataFromUuid(string $uuid): array
    {
        return $this->records[self::formatUuid($uuid)] ?? [
            'RfcACuentaTerceros' => '',
            'NombreACuentaTerceros' => '',
        ];
    }
}
