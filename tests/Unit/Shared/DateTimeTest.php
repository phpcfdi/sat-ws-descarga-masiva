<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use InvalidArgumentException;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class DateTimeTest extends TestCase
{
    public function testCreateUsingTimeZoneZulu(): void
    {
        // remember that per bootstrap default time zone is America/Mexico_City
        $date = new DateTime('2019-01-14T04:23:24.000Z');
        $this->assertSame('2019-01-14T04:23:24.000Z', $date->formatSat());
        $this->assertSame('2019-01-13T22:23:24.000CST', $date->formatDefaultTimeZone());
    }

    public function testCreateWithoutTimeZone(): void
    {
        // remember that per bootstrap default time zone is America/Mexico_City
        $date = new DateTime('2019-01-13 22:23:24'); // as it does not include time zone is created as default
        $this->assertSame('2019-01-14T04:23:24.000Z', $date->formatSat());
        $this->assertSame('2019-01-13T22:23:24.000CST', $date->formatDefaultTimeZone());
    }

    public function testFormatSatUsesZuluTimeZone(): void
    {
        // remember that per bootstrap default time zone is America/Mexico_City
        $date = new DateTime('2019-01-13 22:23:24'); // as it does not include time zone is created as default
        $this->assertSame('2019-01-14T04:23:24.000Z', $date->formatSat());
        $this->assertSame('2019-01-13T22:23:24.000CST', $date->formatDefaultTimeZone());
    }

    public function testCreateDateTimeWithTimestamp(): void
    {
        $date = new DateTime(316569600);
        $this->assertSame('1980-01-13T00:00:00.000Z', $date->formatSat());
    }

    public function testCreateDateTimeWithInvalidStringValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to create a Datetime("foo")');
        new DateTime('foo');
    }

    public function testCreateDateTimeWithInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to create a Datetime');
        new DateTime([]);
    }
}
