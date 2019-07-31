<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit;

use PhpCfdi\SatWsDescargaMasiva\Example;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testAssertIsworking()
    {
        $example = new Example();
        $this->assertInstanceOf(Example::class, $example);
        $this->markTestSkipped('The unit test environment is working');
    }
}
