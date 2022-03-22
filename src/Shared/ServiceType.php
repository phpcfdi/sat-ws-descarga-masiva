<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;
use JsonSerializable;

/**
 * @method static static cfdi()
 * @method static static retenciones()
 *
 * @method bool isCfdi()
 * @method bool isRetenciones()
 */
final class ServiceType extends Enum implements JsonSerializable
{
    public function equalTo(self $serviceType): bool
    {
        return $this->value() === $serviceType->value();
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
