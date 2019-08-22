<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

use Countable;
use Generator;
use RuntimeException;
use Throwable;
use ZipArchive;

/** @internal */
abstract class AbstractPackageReader implements Countable
{
    /**
     * @param string $contents
     * @return bool
     */
    abstract protected function filterContents(string &$contents): bool;

    /**
     * @param string $filename
     * @return bool
     */
    abstract protected function filterEntryFilename(string $filename): bool;

    /** @var ZipArchive */
    private $zip;

    /** @var bool */
    private $removeOnDestruct;

    /** @var string */
    private $filename;

    /**
     * @param string $filename
     * @throws RuntimeException Could not open zip file
     */
    public function __construct(string $filename)
    {
        $this->zip = new ZipArchive();
        $zipCode = $this->zip->open($filename, ZipArchive::CREATE);
        if (true !== $zipCode) {
            throw new RuntimeException(sprintf('Could not open zip file (code %s)', $zipCode));
        }

        $this->filename = $filename;
        $this->removeOnDestruct = false;
    }

    public function __destruct()
    {
        // destruct does not enter if the object was not fully constructed
        $this->zip->close();
        if ($this->removeOnDestruct && file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    public static function createFromContents(string $content): self
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection will check and throw exception */
        $tmpfile = @tempnam(sys_get_temp_dir(), 'TMP_');
        if (false === $tmpfile) {
            /** @codeCoverageIgnore */
            throw new RuntimeException('Could not create the temporary file');
        }

        /** @noinspection PhpUsageOfSilenceOperatorInspection will check and throw exception */
        $written = @file_put_contents($tmpfile, $content);
        if (false === $written) {
            /** @codeCoverageIgnore */
            throw new RuntimeException('Could not write in temporary file');
        }

        try {
            $zip = new static($tmpfile);
        } catch (Throwable $exception) {
            unlink($tmpfile);
            throw $exception;
        }

        $zip->removeOnDestruct = true;
        return $zip;
    }

    public function count(): int
    {
        return iterator_count($this->fileContents());
    }

    /**
     * Generates the list of name => contents and yield only entries that pass name and content filters
     *
     * @return Generator|string[] pair of file name and contents
     */
    public function fileContents()
    {
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $filename = strval($this->zip->getNameIndex($i));
            if ('' === $filename) {
                /** @codeCoverageIgnore */
                continue; // cannot get the file name
            }
            if (! $this->filterEntryFilename($filename)) {
                continue; // did not pass the filename filter
            }

            $contents = $this->zip->getFromName($filename);
            if (false === $contents || ! $this->filterContents($contents)) {
                unset($contents); // release memory as it was filtered
                continue; // did not pass the filename filter
            }

            yield $filename => $contents;
        }
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
