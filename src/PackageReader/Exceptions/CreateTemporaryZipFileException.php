<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions;

use RuntimeException;
use Throwable;

class CreateTemporaryZipFileException extends RuntimeException implements PackageReaderException
{
    private function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function create(string $message, ?Throwable $previous = null): self
    {
        return new self($message, $previous);
    }
}
