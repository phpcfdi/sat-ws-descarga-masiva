<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\AbstractAction;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\Argument;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\Arguments;
use RuntimeException;

class Query extends AbstractAction
{
    public function run(string ...$parameters): void
    {
        $arguments = $this->createArguments();

        ['matched' => $values, 'unmatched' => $unmatched] = $arguments->parseParameters($parameters);
        if ([] !== $unmatched) {
            throw new RuntimeException(sprintf('Unmatched arguments %s', implode(', ', $unmatched)));
        }

        // period
        $period = new DateTimePeriod(new DateTime($values['s'] ?? ''), new DateTime($values['u'] ?? ''));
        // download type
        if ('issued' === strval($values['d'] ?? '')) {
            $downloadType = DownloadType::issued();
        } elseif ('received' === strval($values['d'] ?? '')) {
            $downloadType = DownloadType::received();
        } else {
            throw new RuntimeException("Invalid download type");
        }
        // request type
        if ('metadata' === strval($values['r'] ?? '')) {
            $requestType = RequestType::metadata();
        } elseif ('cfdi' === strval($values['r'] ?? '')) {
            $requestType = RequestType::cfdi();
        } else {
            throw new RuntimeException("Invalid request type");
        }
        // query
        $query = new QueryParameters($period, $downloadType, $requestType);

        $this->stdout(...[
            'Query:',
            '  Since: ' . $query->getDateTimePeriod()->getStart()->formatDefaultTimeZone(),
            '  Until: ' . $query->getDateTimePeriod()->getEnd()->formatDefaultTimeZone(),
            '  Download type: ' . $query->getDownloadType()->value(),
            '  Request type: ' . $query->getRequestType()->value(),
        ]);

        $service = $this->createService();
        $result = $service->query($query);

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
