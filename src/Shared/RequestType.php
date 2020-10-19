<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;
use JsonSerializable;

/**
 * Defines the request type (cfdi or metadata)
 *
 * @method static self cfdi()
 * @method static self metadata()
 *
 * @method bool isCfdi()
 * @method bool isMetadata()
 */
final class RequestType extends Enum implements JsonSerializable
{
    protected static function overrideValues(): array
    {
        return [
            'cfdi' => 'CFDI',
            'metadata' => 'Metadata',
        ];
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
