<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use InvalidArgumentException;
use PhpCfdi\SatWsDescargaMasiva\Shared\CfdiUuid;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class CfdiUuidTest extends TestCase
{
    public function testCreateWithCorrectValue(): void
    {
        $value = '96623061-61fe-49de-b298-c7156476aa8b';
        $uuid = CfdiUuid::create($value);
        $this->assertSame($value, $uuid->getValue());
        $this->assertFalse($uuid->isEmpty());
    }

    public function testCreateWithEmptyValue(): void
    {
        $uuid = CfdiUuid::empty();
        $this->assertEmpty($uuid->getValue());
        $this->assertTrue($uuid->isEmpty());
    }

    public function testCreateWithUpperCaseValue(): void
    {
        $value = '96623061-61FE-49DE-B298-C7156476AA8B';
        $uuid = CfdiUuid::create($value);
        $this->assertSame(strtolower($value), $uuid->getValue());
    }

    /** @return array<string, array{string}> */
    public function providerInvalidValues(): array
    {
        return [
            'empty' => [''],
            'no dashes' => ['9662306161fe49deb298c7156476aa8b'],
            'invalid char' => ['x6623061-61fe-49de-b298-c7156476aa8b'],
            'smaller' => ['x6623061-61fe-49de-b298-c7156476aa8'],
            'bigger' => ['x6623061-61fe-49de-b298-c7156476aa8bb'],
        ];
    }

    /** @dataProvider providerInvalidValues */
    public function testConstructWithInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not have the correct format');
        CfdiUuid::create($value);
    }

    /** @dataProvider providerInvalidValues */
    public function testCheckInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not have the correct format');
        CfdiUuid::check($value);
    }

    /** @dataProvider providerInvalidValues */
    public function testParseInvalidValue(string $value): void
    {
        $this->assertNull(CfdiUuid::parse($value));
    }

    public function testJsonSerialize(): void
    {
        $value = '96623061-61fe-49de-b298-c7156476aa8b';
        $expectedJson = json_encode($value);
        $uuid = CfdiUuid::create($value);
        $this->assertSame($expectedJson, json_encode($uuid));
    }
}
