<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

use Generator;

class MetadataPackageReader extends AbstractPackageReader
{
    /**
     * @return Generator|MetadataItem[]
     */
    public function metadata()
    {
        foreach ($this->fileContents() as $content) {
            $reader = $this->createMetadataContent($content);
            foreach ($reader->eachItem() as $item) {
                yield $item;
            }
        }
    }

    protected function filterEntryFilename(string $filename): bool
    {
        if (boolval(preg_match('/^[\w\-]{36}_[\d]+\.txt$/i', $filename))) {
            return true;
        }
        return false;
    }

    protected function filterContents(string &$contents): bool
    {
        if ('Uuid~RfcEmisor~' === substr($contents, 0, 15)) {
            return true;
        }
        return false;
    }

    public function createMetadataContent(string $content): MetadataContent
    {
        return MetadataContent::createFromContents($content);
    }
}
