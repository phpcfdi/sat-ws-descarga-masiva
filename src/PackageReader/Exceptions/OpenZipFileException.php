<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions;

use RuntimeException;
use Throwable;

class OpenZipFileException extends RuntimeException implements PackageReaderException
{
    /** @var string */
    private $filename;

    private function __construct(string $message, int $code, string $filename, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->filename = $filename;
    }

    public static function create(string $filename, int $code, Throwable $previous = null): self
    {
        return new self(sprintf('Unable to open Zip file %s', $filename), $code, $filename, $previous);
    }

    public function getFileName(): string
    {
        return $this->filename;
    }
}
