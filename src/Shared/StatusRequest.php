<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

/**
 * @method bool isAccepted()
 * @method bool isInProgress()
 * @method bool isFinished()
 * @method bool isFailure()
 * @method bool isRejected()
 * @method bool isExpired()
 */
final class StatusRequest extends OpenEnum
{
    protected const VALUES = [
        1 => ['name' => 'Accepted', 'message' => 'Aceptada'],
        2 => ['name' => 'InProgress', 'message' => 'En proceso'],
        3 => ['name' => 'Finished', 'message' => 'Terminada'],
        4 => ['name' => 'Failure', 'message' => 'Error'],
        5 => ['name' => 'Rejected', 'message' => 'Rechazada'],
        6 => ['name' => 'Expired', 'message' => 'Vencida'],
    ];
}
