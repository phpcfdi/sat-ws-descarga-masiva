<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;
use JsonSerializable;

/**
 * Defines the request type (cfdi or metadata)
 *
 * @method static self undefined()
 * @method static self active()
 * @method static self cancelled()
 *
 * @method bool isUndefined()
 * @method bool isActive()
 * @method bool isCancelled()
 */
final class DocumentStatus extends Enum implements JsonSerializable
{
    protected static function overrideValues(): array
    {
        return [
            'undefined' => '',
            'active' => '1',
            'cancelled' => '0',
        ];
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
