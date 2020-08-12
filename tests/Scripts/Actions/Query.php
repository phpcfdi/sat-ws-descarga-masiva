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
        $period = DateTimePeriod::create(DateTime::create($values['s'] ?? ''), DateTime::create($values['u'] ?? ''));
        // download type
        if ('issued' === strval($values['d'] ?? '')) {
            $downloadType = DownloadType::issued();
        } elseif ('received' === strval($values['d'] ?? '')) {
            $downloadType = DownloadType::received();
        } else {
            throw new RuntimeException('Invalid download type');
        }
        // request type
        if ('metadata' === strval($values['r'] ?? '')) {
            $requestType = RequestType::metadata();
        } elseif ('cfdi' === strval($values['r'] ?? '')) {
            $requestType = RequestType::cfdi();
        } else {
            throw new RuntimeException('Invalid request type');
        }
        // query
        $query = QueryParameters::create($period, $downloadType, $requestType);

        $this->stdout(...[
            'Query:',
            '  Since: ' . $query->getPeriod()->getStart()->formatDefaultTimeZone(),
            '  Until: ' . $query->getPeriod()->getEnd()->formatDefaultTimeZone(),
            '  Download type: ' . $query->getDownloadType()->value(),
            '  Request type: ' . $query->getRequestType()->value(),
        ]);

        $service = $this->createService();
        $result = $service->query($query);

        $status = $result->getStatus();
        $this->stdout(...[
            'Result:',
            '  IsAccepted: ' . (($status->isAccepted()) ? 'yes' : 'no'),
            '  Message: ' . $status->getMessage(),
            '  StatusCode: ' . $status->getCode(),
            '  RequestId: ' . $result->getRequestId(),
        ]);
    }

    public function runHelp(): void
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
