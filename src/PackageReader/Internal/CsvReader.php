<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal;

use EmptyIterator;
use Generator;
use Iterator;
use SplTempFileObject;

/**
 * Helper to iterate inside a CSV file
 * The file must have on the first line the headers.
 * The file uses "~" as separator and "|" as text delimiter.
 *
 * @internal
 */
final class CsvReader
{
    public function __construct(private readonly Iterator $iterator)
    {
    }

    public static function createIteratorFromContents(string $contents): Iterator
    {
        if ('' === $contents) {
            return new EmptyIterator();
        }

        $iterator = new SplTempFileObject();
        $iterator->fwrite($contents);
        $iterator->rewind();
        $iterator->setFlags(SplTempFileObject::READ_CSV);
        $iterator->setCsvControl('~', '|');
        return $iterator;
    }

    public static function createFromContents(string $contents): self
    {
        return new self(self::createIteratorFromContents($contents));
    }

    /**
     * @return Generator<array<string, string>>
     */
    public function records(): Generator
    {
        $headers = [];
        foreach ($this->iterator as $data) {
            $data = $this->normalizeData($data);
            if ([] === $data) {
                continue;
            }

            if ([] === $headers) {
                $headers = $data;
                continue;
            }

            yield $this->combine($headers, $data);
        }
    }

    /**
     * @param mixed $data
     * @return array<int|string, string>
     */
    private function normalizeData($data): array
    {
        if (! is_array($data)) {
            return [];
        }
        return array_filter(
            $data,
            fn ($value, $key): bool => (is_int($key)) && is_string($value),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Like array_combine but complement missing values or missing keys (#extra-01, #extra-02, etc...)
     * @param string[] $keys
     * @param string[] $values
     * @return array<string, string>
     */
    public function combine(array $keys, array $values): array
    {
        $countValues = count($values);
        $countKeys = count($keys);
        if ($countKeys > $countValues) {
            $values = array_merge($values, array_fill($countValues, $countKeys - $countValues, ''));
        }
        if ($countValues > $countKeys) {
            for ($i = 1; $i <= $countValues - $countKeys; $i++) {
                $keys[] = sprintf('#extra-%02d', $i);
            }
        }
        return array_combine($keys, $values) ?: [];
    }
}
