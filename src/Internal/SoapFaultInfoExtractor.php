<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Internal;

use PhpCfdi\SatWsDescargaMasiva\WebClient\SoapFaultInfo;

/**
 * Extract information about SoapFault
 *
 * This class is internal, do not use it outside this project
 * @internal
 */
final class SoapFaultInfoExtractor
{
    use InteractsXmlTrait;

    public static function extract(string $source): ?SoapFaultInfo
    {
        return (new self())->obtainFault($source);
    }

    public function obtainFault(string $source): ?SoapFaultInfo
    {
        $env = $this->readXmlElement($source);
        $code = trim($this->findElement($env, 'body', 'fault', 'faultcode')->textContent ?? '');
        $message = trim($this->findElement($env, 'body', 'fault', 'faultstring')->textContent ?? '');
        if ('' === $code && '' === $message) {
            return null;
        }
        return new SoapFaultInfo($code, $message);
    }
}
