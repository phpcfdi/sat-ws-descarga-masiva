<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

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

        $this->stdout(...[
            'Result:',
            '  Status Code Request: ' . $result->getStatusCodeRequest(),
            '  Status Request: ' . $result->getStatusRequest(),
            '  Message: ' . $result->getMessage(),
            '  StatusCode: ' . $result->getStatusCode(),
            '  Number CFDI: ' . $result->getNumberCfdis(),
            '  Packages: ' . implode(', ', $result->getPackages()),
            '  Has error: ' . (($result->hasError()) ? 'yes' : 'no'),
            '  In progress: ' . (($result->inProgress()) ? 'yes' : 'no'),
            '  Is finished: ' . (($result->isFinished()) ? 'yes' : 'no'),
            '  Is accepted: ' . (($result->isAccepted()) ? 'yes' : 'no'),
            '  Is rejected: ' . (($result->isRejected()) ? 'yes' : 'no'),
            '  Is expired: ' . (($result->isExpired()) ? 'yes' : 'no'),
        ]);
    }

    public function help(): void
    {
        $this->stdout('Perform a request, uses the following parameters:');
        $this->stdout(...$this->createArguments()->toArray());
    }

    protected function createArguments(): Arguments
    {
        return new Arguments(...[
            new Argument('r', 'request-id', 'request-id as received by request command'),
        ]);
    }
}
