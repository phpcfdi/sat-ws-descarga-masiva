<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder\Exceptions;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderException;

final class PeriodEndInvalidDateFormatException extends LogicException implements RequestBuilderException
{
    /** @var string */
    private $periodEnd;

    public function __construct(string $periodEnd)
    {
        parent::__construct(sprintf('The end date time "%s" does not have the correct format', $periodEnd));
        $this->periodEnd = $periodEnd;
    }

    public function getPeriodEnd(): string
    {
        return $this->periodEnd;
    }
}
