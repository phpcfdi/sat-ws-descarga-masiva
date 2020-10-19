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
        $start = DateTime::create('2019-01-01 00:00:59');
        $end = DateTime::create('2019-01-01 00:01:00');

        $dateTimePeriod = DateTimePeriod::create($start, $end);
        $this->assertTrue($start->equalsTo($dateTimePeriod->getStart()));
        $this->assertTrue($end->equalsTo($dateTimePeriod->getEnd()));
    }

    public function testCreateWithStringValues(): void
    {
        $startValue = '2019-01-01 00:00:59';
        $endValue = '2019-01-01 00:01:00';
        $dateTimePeriod = DateTimePeriod::createFromValues($startValue, $endValue);
        $this->assertTrue(DateTime::create($startValue)->equalsTo($dateTimePeriod->getStart()));
        $this->assertTrue(DateTime::create($endValue)->equalsTo($dateTimePeriod->getEnd()));
    }

    public function testCreateWithEndDateTimeLessThanStartDateTime(): void
    {
        $start = DateTime::create('2019-01-01 00:00:59');
        $end = DateTime::create('2019-01-01 00:00:55');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The final date must be greater than the initial date');
        DateTimePeriod::create($start, $end);
    }
}
