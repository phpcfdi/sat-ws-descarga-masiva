<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\CfdiFileFilter;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FilteredPackageReader;
use Traversable;

final class CfdiPackageReader implements PackageReaderInterface
{
    private function __construct(private readonly PackageReaderInterface $packageReader)
    {
    }

    public static function createFromFile(string $filename): self
    {
        $packageReader = FilteredPackageReader::createFromFile($filename);
        $packageReader->setFilter(new CfdiFileFilter());
        return new self($packageReader);
    }

    public static function createFromContents(string $content): self
    {
        $packageReader = FilteredPackageReader::createFromContents($content);
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
            yield self::obtainUuidFromXmlCfdi($content) => $content;
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

    public function fileContents(): Traversable
    {
        yield from $this->packageReader->fileContents();
    }

    /**
     * Helper method to extract the UUID from the TimbreFiscalDigital
     */
    public static function obtainUuidFromXmlCfdi(string $xmlContent): string
    {
        $pattern = '/:Complemento.*?:TimbreFiscalDigital.*?UUID="(?<uuid>[-a-zA-Z0-9]{36})"/s';
        $found = preg_match($pattern, $xmlContent, $matches);
        if (false !== $found && isset($matches['uuid'])) {
            return strtolower($matches['uuid']);
        }
        return '';
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        /** @var array<string, mixed> $base */
        $base = $this->packageReader->jsonSerialize();
        return $base + ['cfdis' => iterator_to_array($this->cfdis())];
    }
}
