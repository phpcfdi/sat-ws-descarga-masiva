<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

use Generator;
use Iterator;
use SplTempFileObject;

/** @internal */
class MetadataContent
{
    /** @var Iterator */
    private $iterator;

    /**
     * The $iterator will be used in a foreach loop to create MetadataItems
     * The first iteration must contain an array of header names that will be renames to lower case first letter
     * The next iterations must contain an array with data
     *
     * @param Iterator $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * This method create a SplTempFileObject to store the information
     *
     * @param string $contents
     * @return MetadataContent
     */
    public static function createFromContents(string $contents): self
    {
        // If the temporary file exceeds this size, it will be moved to a file in the system's temp directory
        $iterator = new SplTempFileObject();
        $iterator->fwrite($contents);
        $iterator->rewind();
        $iterator->setFlags(SplTempFileObject::READ_CSV);
        $iterator->setCsvControl('~');
        return new self($iterator);
    }

    /**
     * @return Generator|MetadataItem[]
     */
    public function eachItem()
    {
        $headers = [];
        $onFirstLine = true;
        // process content lines
        foreach ($this->iterator as $data) {
            if (! is_array($data) || 0 === count($data) || [null] === $data) {
                continue;
            }
            if ($onFirstLine) {
                $onFirstLine = false;
                $headers = array_map('lcfirst', $data);
                continue;
            }

            yield $this->createMetadataItem($headers, $data);
        }
    }

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
