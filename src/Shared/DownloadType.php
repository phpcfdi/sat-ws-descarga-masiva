<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;
use JsonSerializable;

/**
 * Defines the download type (issued or received)
 *
 * @method static self issued()
 * @method static self received()
 *
 * @method bool isIssued()
 * @method bool isReceived()
 */
final class DownloadType extends Enum implements JsonSerializable
{
    protected static function overrideValues(): array
    {
        return [
            'issued' => 'RfcEmisor',
            'received' => 'RfcReceptor',
        ];
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
