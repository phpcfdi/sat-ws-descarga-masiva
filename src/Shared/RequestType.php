<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;

/**
 * Defines the request type (cfdi or metadata)
 *
 * @method static self cfdi()
 * @method static self metadata()
 *
 * @method bool isCfdi()
 * @method bool isMetadata()
 */
final class RequestType extends Enum
{
    protected static function overrideValues(): array
    {
        return [
            'cfdi' => 'CFDI',
            'metadata' => 'Metadata',
        ];
    }
}
