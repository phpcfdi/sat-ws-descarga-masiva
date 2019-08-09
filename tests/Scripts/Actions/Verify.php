<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\AbstractAction;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\Argument;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\Arguments;
use RuntimeException;

class Verify extends AbstractAction
{
    public function run(string ...$parameters): void
    {
        $arguments = $this->createArguments();

        ['matched' => $values, 'unmatched' => $unmatched] = $arguments->parseParameters($parameters);
        if ([] !== $unmatched) {
            throw new RuntimeException(sprintf('Unmatched arguments %s', implode(', ', $unmatched)));
        }

        $requestId = strval($values['r'] ?? '');
        $this->stdout('RequestId: ' . $requestId);

        $service = $this->createService();
        $result = $service->verify($requestId);
        $status = $result->getStatus();
        $codeRequest = $result->getCodeRequest();
        $statusRequest = $result->getStatusRequest();

        $this->stdout(...[
            'Result:',
            '  Is accepted: ' . (($status->isAccepted()) ? 'yes' : 'no'),
            '  Message: ' . $status->getMessage(),
            '  StatusCode: ' . $status->getCode(),
            '  Code Request: ' . $codeRequest->getValue() . ' - ' . $codeRequest->getMessage(),
            '  Status Request: ' . $statusRequest->getValue() . ' - ' . $statusRequest->getMessage(),
            '  Number CFDI: ' . $result->getNumberCfdis(),
            '  Packages: ' . implode(', ', $result->getPackagesIds()),
        ]);
    }

    public function help(): void
    {
        $this->stdout('Verify a request id, the result contains codes information and zero, one or more package id');
        $this->stdout(...$this->createArguments()->toArray());
    }

    protected function createArguments(): Arguments
    {
        return new Arguments(...[
            new Argument('r', 'request-id', 'request-id as received by request command'),
        ]);
    }
}
