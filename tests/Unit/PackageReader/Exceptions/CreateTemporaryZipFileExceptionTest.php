<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader\Exceptions;

use Exception;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions\CreateTemporaryZipFileException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class CreateTemporaryZipFileExceptionTest extends TestCase
{
    public function testProperties(): void
    {
        $message = 'x-message';
        $previous = new Exception();
        $exception = CreateTemporaryZipFileException::create($message, $previous);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
