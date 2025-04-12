<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use InvalidArgumentException;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class AbstractRfcFilterTest extends TestCase
{
    public function testCreateWithCorrectValue(): void
    {
        $value = 'XXX01010199A';
        $uuid = RfcFilterImplementation::create($value);
        $this->assertSame($value, $uuid->getValue());
        $this->assertFalse($uuid->isEmpty());
    }

    public function testCreateWithEmptyValue(): void
    {
        $uuid = RfcFilterImplementation::empty();
        $this->assertEmpty($uuid->getValue());
        $this->assertTrue($uuid->isEmpty());
    }

    /** @return array<string, array{string}> */
    public static function providerInvalidValues(): array
    {
        return [
            'empty' => [''],
            'invalid' => ['XXX99120099A'],
        ];
    }

    /** @dataProvider providerInvalidValues */
    public function testConstructWithInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RFC is invalid');
        RfcFilterImplementation::create($value);
    }

    /** @dataProvider providerInvalidValues */
    public function testCheckInvalidValue(string $value): void
    {
        $this->assertFalse(RfcFilterImplementation::check($value));
    }

    public function testJsonSerialize(): void
    {
        $value = 'XXX01010199A';
        $expectedJson = json_encode($value);
        $uuid = RfcFilterImplementation::create($value);
        $this->assertSame($expectedJson, json_encode($uuid));
    }
}
