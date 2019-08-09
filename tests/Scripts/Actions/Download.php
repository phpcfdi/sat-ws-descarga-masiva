<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\AbstractAction;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\Argument;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\Arguments;
use RuntimeException;

class Download extends AbstractAction
{
    public function run(string ...$parameters): void
    {
        $arguments = $this->createArguments();

        ['matched' => $values, 'unmatched' => $unmatched] = $arguments->parseParameters($parameters);
        if ([] !== $unmatched) {
            throw new RuntimeException(sprintf('Unmatched arguments %s', implode(', ', $unmatched)));
        }

        $packageId = strval($values['i'] ?? '');
        $destination = strval($values['d'] ?? '');
        $this->stdout(
            'Download: ',
            '  PackageId: ' . $packageId,
            '  Destination: ' . $destination
        );

        $service = $this->createService();
        $result = $service->download($packageId);

        if ('' !== $destination) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $write = @file_put_contents($destination, $result->getPackageContent());
        } else {
            $write = $result->getPackageLenght();
        }

        $status = $result->getStatus();
        $this->stdout(...[
            'Result:',
            '  Is accepted: ' . (($status->isAccepted()) ? 'yes' : 'no'),
            '  StatusCode: ' . $status->getCode(),
            '  Message: ' . $status->getMessage(),
            '  Package: ' . ((false === $write) ? 'error writting on destination' : "$write bytes"),
        ]);
    }

    public function help(): void
    {
        $this->stdout('Download a package id, the result contains codes information and zero or one package stream');
        $this->stdout(...$this->createArguments()->toArray());
    }

    protected function createArguments(): Arguments
    {
        return new Arguments(...[
            new Argument('i', 'package-id', 'package-id as received by verify command'),
            new Argument('d', 'destination', 'file name store the package contents'),
        ]);
    }
}
