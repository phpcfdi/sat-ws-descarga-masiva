<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\MetadataFileFilter;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FilteredPackageReader;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\MetadataContent;
use Traversable;

final class MetadataPackageReader implements PackageReaderInterface
{
    /** @var PackageReaderInterface */
    private $packageReader;

    private function __construct(PackageReaderInterface $packageReader)
    {
        $this->packageReader = $packageReader;
    }

    public static function createFromFile(string $filename): self
    {
        $packageReader = FilteredPackageReader::createFromFile($filename);
        $packageReader->setFilter(new MetadataFileFilter());
        return new self($packageReader);
    }

    public static function createFromContents(string $contents): self
    {
        $packageReader = FilteredPackageReader::createFromContents($contents);
        $packageReader->setFilter(new MetadataFileFilter());
        return new self($packageReader);
    }

    /**
     * Traverse the metadata items contained in the hole package.
     * The key is the UUID and the content is the MetadataItem
     *
     * @return Traversable<string, MetadataItem>
     */
    public function metadata()
    {
        foreach ($this->packageReader->fileContents() as $content) {
            $reader = MetadataContent::createFromContents($content);
            foreach ($reader->eachItem() as $item) {
                yield $item->uuid => $item;
            }
        }
    }

    public function getFilename(): string
    {
        return $this->packageReader->getFilename();
    }

    public function count(): int
    {
        return iterator_count($this->metadata());
    }

    public function fileContents()
    {
        yield from $this->packageReader->fileContents();
    }
}
