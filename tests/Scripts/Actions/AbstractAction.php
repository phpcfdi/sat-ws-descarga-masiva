<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

use DateTimeImmutable;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Shared\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Tests\WebClient\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Request;
use PhpCfdi\SatWsDescargaMasiva\WebClient\Response;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;

abstract class AbstractAction implements ActionInterface
{
    /** @var FielData */
    private $fielData;

    /** @var Printer */
    public $printer;

    /** @var string */
    public $outputDirectory;

    public function __construct(FielData $fielData, Printer $printer, string $outputDirectory)
    {
        $this->fielData = $fielData;
        $this->printer = $printer;
        $this->outputDirectory = $outputDirectory;
    }

    public function getFielData(): FielData
    {
        return $this->fielData;
    }

    public function createFiel(): Fiel
    {
        return $this->fielData->createFiel();
    }

    public function stdout(...$lines): void
    {
        $this->printer->stdout(...$lines);
    }

    public function stderr(...$lines): void
    {
        $this->printer->stderr(...$lines);
    }

    public function createService(): Service
    {
        return new Service($this->createFiel(), $this->createWebClient());
    }

    private function createWebClient(): WebClientInterface
    {
        $jsonPrinter = null;
        if ('' !== $this->outputDirectory) {
            /** @param Request|Response $payload */
            $jsonPrinter = function ($payload): void {
                $now = new DateTimeImmutable();
                $jsonFile = sprintf(
                    '%s/%s_%s.json',
                    $this->outputDirectory,
                    $now->format('Ymd-His.u'),
                    strtolower(basename(str_replace('\\', '/', get_class($payload))))
                );
                file_put_contents($jsonFile, json_encode($payload, JSON_PRETTY_PRINT));
                $bodyFile = str_replace('.json', '.xml', $jsonFile);
                $xmlBody = $payload->getBody();
                if ('' !== $xmlBody) {
                    file_put_contents($bodyFile, $xmlBody);
                }
            };
        }
        return new GuzzleWebClient(null, $jsonPrinter, $jsonPrinter);
    }
}
