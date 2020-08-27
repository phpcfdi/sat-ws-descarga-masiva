<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader\Exceptions;

use Exception;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions\OpenZipFileException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class OpenZipFileExceptionTest extends TestCase
{
    public function testProperties(): void
    {
        $filename = 'filename';
        $code = 3;
        $previous = new Exception();
        $exception = OpenZipFileException::create($filename, $code, $previous);
        $this->assertStringContainsString('Unable to open Zip file', $exception->getMessage());
        $this->assertSame($filename, $exception->getFileName());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
