<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use InvalidArgumentException;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class DateTimePeriodTest extends TestCase
{
    public function testCreateWithCorrectStartDateTimeAndEndDateTime(): void
    {
        $start = new DateTime('2019-01-01 00:00:59');
        $end = new DateTime('2019-01-01 00:01:00');

        $dateTimePeriod = new DateTimePeriod($start, $end);
        $this->assertTrue($start->equalsTo($dateTimePeriod->getStart()));
        $this->assertTrue($end->equalsTo($dateTimePeriod->getEnd()));
    }

    public function testCreateWithEndDateTimeLessThanStartDateTime(): void
    {
        $start = new DateTime('2019-01-01 00:00:59');
        $end = new DateTime('2019-01-01 00:00:55');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The final date must be greater than the initial date');
        new DateTimePeriod($start, $end);
    }
}
