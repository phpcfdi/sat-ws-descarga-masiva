<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use InvalidArgumentException;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class UuidTest extends TestCase
{
    public function testCreateWithCorrectValue(): void
    {
        $value = '96623061-61fe-49de-b298-c7156476aa8b';
        $uuid = Uuid::create($value);
        $this->assertSame($value, $uuid->getValue());
        $this->assertFalse($uuid->isEmpty());
    }

    public function testCreateWithEmptyValue(): void
    {
        $uuid = Uuid::empty();
        $this->assertEmpty($uuid->getValue());
        $this->assertTrue($uuid->isEmpty());
    }

    public function testCreateWithUpperCaseValue(): void
    {
        $value = '96623061-61FE-49DE-B298-C7156476AA8B';
        $uuid = Uuid::create($value);
        $this->assertSame(strtolower($value), $uuid->getValue());
    }

    /** @return array<string, array{string}> */
    public static function providerInvalidValues(): array
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
        Uuid::create($value);
    }

    public function testCheckValidValue(): void
    {
        $this->assertTrue(Uuid::check('96623061-61fe-49de-b298-c7156476aa8b'));
    }

    /** @dataProvider providerInvalidValues */
    public function testCheckInvalidValue(string $value): void
    {
        $this->assertFalse(Uuid::check($value));
    }

    public function testJsonSerialize(): void
    {
        $value = '96623061-61fe-49de-b298-c7156476aa8b';
        $expectedJson = json_encode($value);
        $uuid = Uuid::create($value);
        $this->assertSame($expectedJson, json_encode($uuid));
    }
}
