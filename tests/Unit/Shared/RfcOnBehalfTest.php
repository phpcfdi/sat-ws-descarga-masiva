<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\AbstractRfcFilter;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class RfcOnBehalfTest extends TestCase
{
    public function testExtendsAbstractRfcFilter(): void
    {
        $this->assertInstanceOf(AbstractRfcFilter::class, RfcOnBehalf::empty());
    }
}
