<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Download;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadTranslator;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;
use ZipArchive;

final class DownloadTranslatorContentsTest extends TestCase
{
    private const FILE_SIZE_LIMIT = 10 * 1024 * 1024; // 10 MiB

    /** @var string */
    private $hugeZipFile;

    /** @var string */
    private $hugeResponse;

    protected function setUp(): void
    {
        parent::setUp();
        $hugeZipFile = tempnam('', '');
        if (false === $hugeZipFile) {
            throw new LogicException('Unable to create a temporary file');
        }

        $this->hugeZipFile = $hugeZipFile;
        $this->createHugeZipFile($this->hugeZipFile);
        $this->hugeResponse = $this->createHugeResponse($this->hugeZipFile);
        if (strlen($this->hugeResponse) < self::FILE_SIZE_LIMIT) {
            throw new LogicException(
                sprintf('Unable to create a response with size bigger than %s bytes', self::FILE_SIZE_LIMIT)
            );
        }
    }

    protected function tearDown(): void
    {
        unlink($this->hugeZipFile);
        parent::tearDown();
    }

    private function createHugeZipFile(string $destination): void
    {
        $chunkSize = 2 * 1024 * 1024; // 1MB
        $limitSize = self::FILE_SIZE_LIMIT; // 10MB
        $fileCount = intval($limitSize / $chunkSize) + 1;
        $archive = new ZipArchive();
        $archive->open($destination, ZipArchive::OVERWRITE);
        for ($i = 1; $i < $fileCount; $i++) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $archive->addFromString(sprintf('%d.txt', $i), random_bytes($chunkSize));
        }
        $archive->close();
    }

    private function createHugeResponse(string $contentFile): string
    {
        $template = $this->fileContents('download/response-with-package-template.xml');
        $template = (string) preg_replace('/>\s+</', '><', $template);
        $template = str_replace('?>', "?>\n", $template);
        $search = '<Paquete />';
        $strpos = (int) strpos($template, $search);
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $template = substr($template, 0, $strpos)
            . '<Paquete>' . base64_encode((string) file_get_contents($contentFile)) . '</Paquete>'
            . substr($template, $strpos + strlen($search));
        // file_put_contents('/tmp/sample-response.xml', $template);
        return $template;
    }

    public function testCreateDownloadResultFromHugeResponse(): void
    {
        $translator = new DownloadTranslator();

        $result = $translator->createDownloadResultFromSoapResponse($this->hugeResponse);

        $this->assertSame(
            sha1_file($this->hugeZipFile),
            sha1($result->getPackageContent()),
            'Extracted package contents for huge file are not the expected',
        );
    }
}
