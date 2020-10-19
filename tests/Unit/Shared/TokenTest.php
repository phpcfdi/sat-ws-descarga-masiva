<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use InvalidArgumentException;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Shared\Token;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class TokenTest extends TestCase
{
    public function testCreateTokenWithInvalidDates(): void
    {
        $created = DateTime::create();
        $expires = $created->modify('- 1 second');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create a token with expiration lower than creation');
        new Token($created, $expires, '');
    }

    public function testTokenNotExpired(): void
    {
        $created = DateTime::create();
        $expires = $created->modify('+ 5 seconds');
        $token = new Token($created, $expires, '');
        $this->assertFalse($token->isExpired());
    }

    public function testTokenExpired(): void
    {
        $created = DateTime::create('- 10 seconds');
        $expires = $created->modify('+ 5 seconds');
        $token = new Token($created, $expires, '');
        $this->assertTrue($token->isExpired());
    }

    public function testValueNotEmpty(): void
    {
        $created = DateTime::create('- 10 seconds');
        $expires = $created->modify('+ 5 seconds');
        $token = new Token($created, $expires, '');
        $this->assertTrue($token->isValueEmpty());
    }

    public function testValueIsNotEmpty(): void
    {
        $created = DateTime::create('- 10 seconds');
        $expires = $created->modify('+ 5 seconds');
        $token = new Token($created, $expires, 'foo');
        $this->assertFalse($token->isValueEmpty());
    }

    /**
     * @param string $created
     * @param string $expires
     * @param string $value
     * @param bool $expected
     * @testWith ["- 10 seconds", "+ 10 seconds", "foo", true]
     *           ["- 10 seconds", "- 1 seconds", "foo", false]
     *           ["- 10 seconds", "+ 10 seconds", "", false]
     *           ["- 10 seconds", "- 1 seconds", "", false]
     */
    public function testIsValid(string $created, string $expires, string $value, bool $expected): void
    {
        $token = new Token(DateTime::create($created), DateTime::create($expires), $value);
        $this->assertSame($expected, $token->isValid());
    }

    public function testJson(): void
    {
        $created = DateTime::create('2020-01-13T14:15:16-0600');
        $expires = $created->modify('+ 5 seconds');
        $token = new Token($created, $expires, 'x-value');
        $expectedFile = $this->filePath('json/token.json');
        $this->assertJsonStringEqualsJsonFile($expectedFile, json_encode($token) ?: '');
    }
}
