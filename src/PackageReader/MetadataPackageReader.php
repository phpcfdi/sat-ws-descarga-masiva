<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\MetadataFileFilter;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FilteredPackageReader;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\MetadataContent;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\ThirdPartiesRecords;
use Traversable;

final class MetadataPackageReader implements PackageReaderInterface
{
    private readonly ThirdPartiesRecords $thirdParties;

    private function __construct(private readonly PackageReaderInterface $packageReader)
    {
        $this->thirdParties = ThirdPartiesRecords::createFromPackageReader($this->packageReader);
    }

    public static function createFromFile(string $filename): self
    {
        $packageReader = FilteredPackageReader::createFromFile($filename);
        $packageReader->setFilter(new MetadataFileFilter());
        return new self($packageReader);
    }

    public static function createFromContents(string $content): self
    {
        $packageReader = FilteredPackageReader::createFromContents($content);
        $packageReader->setFilter(new MetadataFileFilter());
        return new self($packageReader);
    }

    public function getThirdParties(): ThirdPartiesRecords
    {
        return $this->thirdParties;
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
            $reader = MetadataContent::createFromContents($content, $this->getThirdParties());
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

    public function fileContents(): Traversable
    {
        yield from $this->packageReader->fileContents();
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        /** @var array<string, mixed> $base */
        $base = $this->packageReader->jsonSerialize();
        return $base + ['metadata' => iterator_to_array($this->metadata())];
    }
}
