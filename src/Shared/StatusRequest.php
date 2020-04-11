<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\MicroCatalog\MicroCatalog;

/**
 * @method bool isAccepted()
 * @method bool isInProgress()
 * @method bool isFinished()
 * @method bool isFailure()
 * @method bool isRejected()
 * @method bool isExpired()
 * @method string getMessage()
 * @method string getName()
 */
final class StatusRequest extends MicroCatalog
{
    protected const VALUES = [
        1 => ['name' => 'Accepted', 'message' => 'Aceptada'],
        2 => ['name' => 'InProgress', 'message' => 'En proceso'],
        3 => ['name' => 'Finished', 'message' => 'Terminada'],
        4 => ['name' => 'Failure', 'message' => 'Error'],
        5 => ['name' => 'Rejected', 'message' => 'Rechazada'],
        6 => ['name' => 'Expired', 'message' => 'Vencida'],
    ];

    public static function getEntriesArray(): array
    {
        return self::VALUES;
    }

    public function getEntryValueOnUndefined()
    {
        return ['name' => 'Unknown', 'message' => 'Desconocida'];
    }

    public function getEntryId(): string
    {
        return $this->getName();
    }

    public function getValue(): int
    {
        return intval($this->getEntryIndex());
    }
}
