<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\Enum\Enum;

/**
 *
 * @method static self issued()
 * @method static self received()
 *
 * @method bool isIssued()
 * @method bool isReceived()
 */
class DownloadType extends Enum
{
    protected static function overrideValues(): array
    {
        return [
            'issued' => 'RfcEmisor',
            'received' => 'RfcReceptor',
        ];
    }
}
