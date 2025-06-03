<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal;

use Generator;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataItem;

/**
 * Helper to iterate inside a Metadata CSV file
 *
 * @internal
 */
final class MetadataContent
{
    /** @var CsvReader */
    private $csvReader;

    /** @var ThirdPartiesRecords */
    private $thirdParties;

    /**
     * The $iterator will be used in a foreach loop to create MetadataItems
     * The first iteration must contain an array of header names that will be renamed to lower case first letter
     * The next iterations must contain an array with data
     *
     * @param CsvReader $csvReader
     * @param ThirdPartiesRecords $thirdParties
     */
    public function __construct(CsvReader $csvReader, ThirdPartiesRecords $thirdParties)
    {
        $this->csvReader = $csvReader;
        $this->thirdParties = $thirdParties;
    }

    /**
     * This method apply the preprocessor fixes on the contents
     *
     * @param string $contents
     * @param ThirdPartiesRecords|null $thirdParties
     * @return MetadataContent
     */
    public static function createFromContents(string $contents, ?ThirdPartiesRecords $thirdParties = null): self
    {
        $thirdParties = $thirdParties ?? ThirdPartiesRecords::createEmpty();

        // fix known errors on metadata text file
        $preprocessor = new MetadataPreprocessor($contents);
        $preprocessor->fix();

        $csvReader = CsvReader::createFromContents($preprocessor->getContents());

        return new self($csvReader, $thirdParties);
    }

    /**
     * @return Generator<MetadataItem>
     */
    public function eachItem(): Generator
    {
        foreach ($this->csvReader->records() as $data) {
            $data = $this->thirdParties->addToData($data);
            $data = $this->changeArrayKeysFirstLetterLowerCase($data);
            yield new MetadataItem($data);
        }
    }

    /**
     * @param array<string, string> $data
     * @return array<string, string>
     */
    private function changeArrayKeysFirstLetterLowerCase(array $data): array
    {
        $keys = array_map(function ($key): string {
            return lcfirst((string) $key);
        }, array_keys($data));
        return array_combine($keys, $data);
    }
}
