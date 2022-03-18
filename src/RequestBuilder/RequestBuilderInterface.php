<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder;

use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;

/**
 * The implementors must create the request signed ready to send to the SAT Web Service Descarga Masiva
 * The information about owner like RFC, certificate, private key, etc. are outside the scope of this interface
 */
interface RequestBuilderInterface
{
    /**
     * Creates an authorization signed xml message
     *
     * @param DateTime $created
     * @param DateTime $expires
     * @param string $securityTokenId if empty, the authentication method will create one by its own
     * @return string
     * @throws RequestBuilderException
     */
    public function authorization(DateTime $created, DateTime $expires, string $securityTokenId = ''): string;

    /**
     * Creates a query signed xml message
     *
     * @param QueryParameters $queryParameters
     * @return string
     * @throws RequestBuilderException
     */
    public function query(QueryParameters $queryParameters): string;

    /**
     * Creates a verify signed xml message
     *
     * @param string $requestId
     * @return string
     * @throws RequestBuilderException
     */
    public function verify(string $requestId): string;

    /**
     * Creates a download signed xml message
     *
     * @param string $packageId
     * @return string
     * @throws RequestBuilderException
     */
    public function download(string $packageId): string;
}
