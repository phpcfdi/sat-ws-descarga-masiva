<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\PackageReader\Internal;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Exceptions\OpenZipFileException;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters\NullFileFilter;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FilteredPackageReader;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use ZipArchive;

class FilteredPackageReaderTest extends TestCase
{
    public function testCreateFromFileWithInvalidFile(): void
    {
        $filename = __DIR__;
        $this->expectException(OpenZipFileException::class);
        FilteredPackageReader::createFromFile($filename);
    }

    public function testCreateFromContentWithInvalidContent(): void
    {
        $this->expectException(OpenZipFileException::class);
        FilteredPackageReader::createFromContents('invalid content');
    }

    public function testFileContentsAndCountWithFile(): void
    {
        $archiveFile = (string) tempnam('', '');
        if ('' === $archiveFile) {
            throw new LogicException('Unable to create a temporary file');
        }
        $archive = new ZipArchive();
        $archive->open($archiveFile, ZipArchive::OVERWRITE);
        $archive->addEmptyDir('empty dir');
        $archive->addFromString('empty file.txt', '');
        $archive->addFromString('foo.txt', 'foo');
        $archive->addFromString('sub/bar.txt', 'bar');
        $archive->close();

        $expected = [
            'empty dir/' => '',
            'empty file.txt' => '',
            'foo.txt' => 'foo',
            'sub/bar.txt' => 'bar',
        ];

        $packageReader = FilteredPackageReader::createFromFile($archiveFile);
        $packageReader->setFilter(new NullFileFilter());
        $fileContents = iterator_to_array($packageReader->fileContents());
        $this->assertSame($expected, $fileContents);
        $this->assertCount(count($expected), $packageReader);

        unlink($archiveFile);
    }
}
