<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal;

use Generator;
use Iterator;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\MetadataItem;
use SplTempFileObject;

/**
 * Helper to iterate inside a Metadata CSV file
 *
 * @internal
 */
final class MetadataContent
{
    /** @var Iterator<mixed> */
    private $iterator;

    /** @var ThirdPartiesRecords */
    private $thirdParties;

    /**
     * The $iterator will be used in a foreach loop to create MetadataItems
     * The first iteration must contain an array of header names that will be renames to lower case first letter
     * The next iterations must contain an array with data
     *
     * @param Iterator<mixed> $iterator
     * @param ThirdPartiesRecords $thirdParties
     */
    public function __construct(Iterator $iterator, ThirdPartiesRecords $thirdParties)
    {
        $this->iterator = $iterator;
        $this->thirdParties = $thirdParties;
    }

    /**
     * This method fix the content and create a SplTempFileObject to store the information
     *
     * @param string $contents
     * @param ThirdPartiesRecords|null $thirdParties
     * @return MetadataContent
     */
    public static function createFromContents(string $contents, ThirdPartiesRecords $thirdParties = null): self
    {
        $thirdParties = $thirdParties ?? ThirdPartiesRecords::createEmpty();

        // fix known errors on metadata text file
        $preprocessor = new MetadataPreprocessor($contents);
        $preprocessor->fix();

        // If the temporary file exceeds this size, it will be moved to a file in the system's temp directory
        $iterator = new SplTempFileObject();
        $iterator->fwrite($preprocessor->getContents());
        $iterator->rewind();
        $iterator->setFlags(SplTempFileObject::READ_CSV);
        $iterator->setCsvControl('~', '|');
        return new self($iterator, $thirdParties);
    }

    /**
     * @return Generator<MetadataItem>
     */
    public function eachItem(): Generator
    {
        $headers = [];
        $onFirstLine = true;
        // process content lines
        foreach ($this->iterator as $data) {
            if (! is_array($data) || [] === $data || [null] === $data) {
                continue;
            }

            if ($onFirstLine) {
                $onFirstLine = false;
                $headers = $this->thirdParties->addToHeaders($data);
                $headers = array_map('lcfirst', $headers);
                continue;
            }

            $data = $this->thirdParties->addToData($data);

            yield $this->createMetadataItem($headers, $data);
        }
    }

    /**
     * @param array<string> $headers
     * @param array<string> $values
     * @return MetadataItem
     */
    public function createMetadataItem(array $headers, array $values): MetadataItem
    {
        $countValues = count($values);
        $countHeaders = count($headers);
        if ($countHeaders > $countValues) {
            $values = array_merge($values, array_fill($countValues, $countHeaders - $countValues, ''));
        }
        if ($countValues > $countHeaders) {
            for ($i = 1; $i <= $countValues - $countHeaders; $i++) {
                $headers[] = sprintf('#extra-%02d', $i);
            }
        }
        return new MetadataItem(array_combine($headers, $values) ?: []);
    }
}
