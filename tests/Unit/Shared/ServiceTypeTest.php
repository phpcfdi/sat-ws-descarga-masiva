<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Shared;

use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

final class ServiceTypeTest extends TestCase
{
    public function testEqualTo(): void
    {
        $firstCfdi = ServiceType::cfdi();

        $this->assertTrue($firstCfdi->equalTo($firstCfdi));
        $this->assertTrue($firstCfdi->equalTo(ServiceType::cfdi()));
        $this->assertFalse($firstCfdi->equalTo(ServiceType::retenciones()));
    }

    /** @return array<string, array{ServiceType}> */
    public static function providerServiceTypes(): array
    {
        return [
            'cfdi' => [ServiceType::cfdi()],
            'retenciones' => [ServiceType::retenciones()],
        ];
    }

    /** @dataProvider providerServiceTypes */
    public function testJsonEncode(ServiceType $serviceType): void
    {
        $json = json_encode($serviceType);
        $expected = json_encode($serviceType->value());
        $this->assertSame($expected, $json);
    }
}
