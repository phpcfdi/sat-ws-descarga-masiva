<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Internal;

use DOMDocument;
use InvalidArgumentException;
use PhpCfdi\SatWsDescargaMasiva\Internal\InteractsXmlTrait;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class InteractsXmlTraitTest extends TestCase
{
    public function testFindElementExpectingOne(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'verificaSolicitudDescargaResponse', 'verificaSolicitudDescargaResult'];
        $this->assertCount(1, $specimen->findElements($root, ...$search));
        $this->assertSame(
            $specimen->findElements($root, ...$search)[0],
            $specimen->findElement($root, ...$search)
        );
    }

    public function testReadXmlDocumentWithoutContentThrowsException(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot load an xml with empty content');
        $specimen->readXmlDocument('');
    }

    public function testReadXmlElementWithoutDocumentRootElementThrowsException(): void
    {
        $specimen = new class() {
            use InteractsXmlTrait;

            public function readXmlDocument(string $source): DOMDocument
            {
                unset($source);
                return new DOMDocument();
            }
        };
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot load an xml without document element');
        $specimen->readXmlElement('');
    }

    public function testFindElementExpectingNone(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'foo', 'verificaSolicitudDescargaResult'];
        $this->assertCount(0, $specimen->findElements($root, ...$search));
        $this->assertNull($specimen->findElement($root, ...$search));
    }

    public function testFindElementExpectingTwo(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'verificaSolicitudDescargaResponse', 'verificaSolicitudDescargaResult', 'idsPaquetes'];
        $this->assertCount(2, $specimen->findElements($root, ...$search));
        $this->assertSame(
            $specimen->findElements($root, ...$search)[0],
            $specimen->findElement($root, ...$search)
        );
    }

    public function testFindContentWithKnownData(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'verificaSolicitudDescargaResponse', 'verificaSolicitudDescargaResult', 'idsPaquetes'];
        $expectedContent = '4e80345d-917f-40bb-a98f-4a73939343c5_01';
        $this->assertSame($expectedContent, $specimen->findContent($root, ...$search));
    }

    public function testFindContentWithNotFoundElement(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'verificaSolicitudDescargaResponse', 'FOO', 'idsPaquetes'];
        $this->assertSame('', $specimen->findContent($root, ...$search));
    }

    public function testFindContentWithChildrenWithContentsButNoContentByItsOwn(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'verificaSolicitudDescargaResponse', 'verificaSolicitudDescargaResult'];
        $this->assertSame('', $specimen->findContent($root, ...$search));
    }

    public function testFindContents(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'verificaSolicitudDescargaResponse', 'verificaSolicitudDescargaResult', 'idsPaquetes'];
        $expectedContents = [
            '4e80345d-917f-40bb-a98f-4a73939343c5_01',
            '4e80345d-917f-40bb-a98f-4a73939343c5_02',
        ];
        $this->assertSame($expectedContents, $specimen->findContents($root, ...$search));
    }

    public function testFindAttributesExpectingResults(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'verificaSolicitudDescargaResponse', 'verificaSolicitudDescargaResult'];
        $expectedContents = [
            'codestatus' => '5000',
            'estadosolicitud' => '3',
            'codigoestadosolicitud' => '5000',
            'numerocfdis' => '12345',
            'mensaje' => 'Solicitud Aceptada',
        ];
        $this->assertSame($expectedContents, $specimen->findAttributes($root, ...$search));
    }

    public function testFindAttributesOnNonExistentNode(): void
    {
        $specimen = new InteractsXmlTraitSpecimen();
        $content = $this->fileContents('verify/response-2-packages.xml');
        $root = $specimen->readXmlElement($content);

        $search = ['body', 'FOO', 'verificaSolicitudDescargaResult'];
        $this->assertSame([], $specimen->findAttributes($root, ...$search));
    }
}
