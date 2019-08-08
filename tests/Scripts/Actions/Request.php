<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use RuntimeException;

class Request extends AbstractAction
{
    public function run(string ...$parameters): void
    {
        $arguments = $this->createArguments();

        ['matched' => $values, 'unmatched' => $unmatched] = $arguments->parseParameters($parameters);
        if ([] !== $unmatched) {
            throw new RuntimeException(sprintf('Unmatched arguments %s', implode(', ', $unmatched)));
        }

        $query = new QueryParameters(
            new DateTimePeriod(new DateTime($values['s'] ?? ''), new DateTime($values['u'] ?? '')),
            new DownloadType($values['d'] ?? DownloadType::issued()),
            new RequestType($values['r'] ?? RequestType::cfdi())
        );
        $this->stdout(...[
            'Query:',
            '  Since: ' . $query->getDateTimePeriod()->getStart()->formatDefaultTimeZone(),
            '  Until: ' . $query->getDateTimePeriod()->getEnd()->formatDefaultTimeZone(),
            '  Download type: ' . $query->getDownloadType()->value(),
            '  Request type: ' . $query->getRequestType()->value(),
        ]);

        $service = $this->createService();
        $result = $service->downloadRequest($query);

        $this->stdout(...[
            'Result:',
            '  IsAccepted: ' . (($result->isAccepted()) ? 'yes' : 'no'),
            '  Message: ' . $result->getMessage(),
            '  StatusCode: ' . $result->getStatusCode(),
            '  RequestId: ' . $result->getRequestId(),
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
            new Argument('s', 'since', 'start date time expression for period'),
            new Argument('u', 'until', 'end date time expression for period'),
            new Argument('d', 'download-type', '"issued" or "received"'),
            new Argument('r', 'request-type', '"cfdi" or "metadata"'),
        ]);
    }
}
