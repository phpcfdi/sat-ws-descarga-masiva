<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions\CreateTemporaryZipFileException;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions\OpenZipFileException;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\FileFilterInterface;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\NullFileFilter;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\PackageReaderInterface;
use Throwable;
use Traversable;
use ZipArchive;

/**
 * Generic package reader, depends on a filter to know if the file in the archive is valid.
 *
 * @internal
 */
final class FilteredPackageReader implements PackageReaderInterface
{
    private bool $removeOnDestruct = false;

    private FileFilterInterface $filter;

    private function __construct(private readonly string $filename, private readonly ZipArchive $archive)
    {
        $this->filter = new NullFileFilter();
    }

    public function __destruct()
    {
        if ($this->removeOnDestruct) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($this->filename);
        }
    }

    /** @inheritDoc */
    public static function createFromFile(string $filename): self
    {
        $archive = new ZipArchive();
        $zipCode = $archive->open($filename);
        if (true !== $zipCode) {
            throw OpenZipFileException::create($filename, $zipCode);
        }

        return new self($filename, $archive);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore Unable to produce code coverage for error handling
     */
    public static function createFromContents(string $content): self
    {
        // create temp file
        try {
            $tmpfile = tempnam(sys_get_temp_dir(), '');
        } catch (Throwable $exception) {
            throw CreateTemporaryZipFileException::create('Cannot create a temporary file', $exception);
        }
        if (false === $tmpfile) {
            throw CreateTemporaryZipFileException::create('Cannot not create a temporary file');
        }

        // write contents
        try {
            $write = file_put_contents($tmpfile, $content);
        } catch (Throwable $exception) {
            throw CreateTemporaryZipFileException::create('Cannot store contents on temporary file', $exception);
        }
        if (false === $write) {
            throw CreateTemporaryZipFileException::create('Cannot store contents on temporary file');
        }

        // build object
        try {
            $package = self::createFromFile($tmpfile);
        } catch (OpenZipFileException $exception) {
            unlink($tmpfile);
            throw $exception;
        }

        // set special flag to remove file when this object is destroyed
        $package->removeOnDestruct = true;
        return $package;
    }

    public function fileContents(): Traversable
    {
        $archive = $this->getArchive();
        $filter = $this->getFilter();
        for ($i = 0; $i < $archive->numFiles; $i++) {
            $filename = $archive->getNameIndex($i);
            if (false === $filename || ! $filter->filterFilename($filename)) {
                continue; // did not pass the filename filter
            }

            $contents = $archive->getFromName($filename);
            if (false === $contents || ! $filter->filterContents($contents)) {
                unset($contents); // release memory as it was filtered
                continue; // did not pass the filename filter
            }

            yield $filename => $contents;
        }
    }

    public function count(): int
    {
        return iterator_count($this->fileContents());
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    protected function getArchive(): ZipArchive
    {
        return $this->archive;
    }

    public function getFilter(): FileFilterInterface
    {
        return $this->filter;
    }

    public function setFilter(FileFilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    public function changeFilter(FileFilterInterface $filter): FileFilterInterface
    {
        $previous = $this->getFilter();
        $this->setFilter($filter);
        return $previous;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'source' => $this->getFilename(),
            'files' => iterator_to_array($this->fileContents()),
        ];
    }
}
