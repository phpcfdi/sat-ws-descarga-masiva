<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\CfdiFileFilter;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FilteredPackageReader;
use Traversable;

final class CfdiPackageReader implements PackageReaderInterface
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
        $packageReader->setFilter(new CfdiFileFilter());
        return new self($packageReader);
    }

    public static function createFromContents(string $contents): self
    {
        $packageReader = FilteredPackageReader::createFromContents($contents);
        $packageReader->setFilter(new CfdiFileFilter());
        return new self($packageReader);
    }

    /**
     * Traverse the CFDI contained in the hole package
     * The key is the UUID and the content is the XML
     *
     * @return Traversable<string, string>
     */
    public function cfdis()
    {
        foreach ($this->packageReader->fileContents() as $content) {
            yield $this->obtainUuidFromXmlCfdi($content) => $content;
        }
    }

    public function getFilename(): string
    {
        return $this->packageReader->getFilename();
    }

    public function count(): int
    {
        return iterator_count($this->cfdis());
    }

    public function fileContents()
    {
        yield from $this->packageReader->fileContents();
    }

    /**
     * Helper method to extract the UUID from the TimbreFiscalDigital
     *
     * @param string $xmlContent
     * @return string
     */
    public static function obtainUuidFromXmlCfdi(string $xmlContent): string
    {
        $found = preg_match('/TimbreFiscalDigital.*?UUID="(?<uuid>[-a-zA-Z0-9]{36})"/s', $xmlContent, $matches);
        if (false !== $found && is_array($matches)) {
            return strtolower($matches['uuid'] ?? '');
        }
        return '';
    }
}
