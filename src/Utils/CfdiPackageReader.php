<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Utils;

use RuntimeException;
use ZipArchive;

class CfdiPackageReader
{
    /** @var string */
    private $content;

    /** @var ZipArchive */
    private $zip;

    /** @var string */
    private $tmpfile;

    /**
     * @param string $content
     * @throws \RuntimeException
     */
    public function __construct(string $content)
    {
        $this->content = $content;

        $this->tmpfile = $this->writeTemporaryFile($this->content);

        $this->zip = new ZipArchive();

        if (true !== $this->zip->open($this->tmpfile, ZipArchive::CREATE)) {
            throw new RuntimeException('Count not open zip');
        }
    }

    protected function writeTemporaryFile(string $content): string
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'TMP_');

        if (false === $tmpfile) {
            throw new RuntimeException('Count not create the temporary file');
        }

        $written = file_put_contents($tmpfile, $content);

        if (false === $written) {
            throw new RuntimeException('Count not write in temporary file');
        }

        return $tmpfile;
    }

    protected function cfdiKeyContents(): string
    {
        return 'cfdi:Comprobante';
    }

    protected function fileKey(): string
    {
        return '.xml';
    }

    public function cfdis() : \Generator
    {
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $filename = $this->zip->getNameIndex($i);
            if (false === $filename || false === strpos($filename, $this->fileKey())) {
                continue;
            }

            $contents = $this->zip->getFromName($filename);

            if (false === $contents || false === strpos($contents, $this->cfdiKeyContents())) {
                continue;
            }

            yield $contents;
        }
    }

    public function __destruct()
    {
        unlink($this->tmpfile);
    }
}
