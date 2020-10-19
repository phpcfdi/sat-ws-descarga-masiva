<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Query;

use JsonSerializable;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryResult;
use PhpCfdi\SatWsDescargaMasiva\Shared\StatusCode;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class QueryResultTest extends TestCase
{
    public function testProperties(): void
    {
        $statusCode = new StatusCode(9, 'foo');
        $requestId = 'x-request-id';
        $result = new QueryResult($statusCode, $requestId);
        $this->assertSame($statusCode, $result->getStatus());
        $this->assertSame($requestId, $result->getRequestId());
    }

    public function testEmptyRequestId(): void
    {
        $requestId = '';
        $result = new QueryResult(new StatusCode(9, 'foo'), $requestId);
        $this->assertSame($requestId, $result->getRequestId());
    }

    public function testJson(): void
    {
        $statusCode = new StatusCode(9, 'foo');
        $requestId = 'x-request-id';
        $result = new QueryResult($statusCode, $requestId);
        $this->assertInstanceOf(JsonSerializable::class, $result);
        $expectedFile = $this->filePath('json/query-result.json');
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($result) ?: '');
    }
}
