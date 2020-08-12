<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\RequestBuilder;

/**
 * The implementors must create the request signed ready to send to the SAT Web Service Descarga Masiva
 * The information about owner like RFC, certificate, private key, etc. are outside the scope of this interface
 */
interface RequestBuilderInterface
{
    /**
     * Creates an authorization signed xml message
     *
     * @param string $created must use SAT format 'Y-m-d\TH:i:s.000T'
     * @param string $expires must use SAT format 'Y-m-d\TH:i:s.000T'
     * @param string $securityTokenId if empty, the authentication method will create one by its own
     * @return string
     */
    public function authorization(string $created, string $expires, string $securityTokenId = ''): string;

    /**
     * Creates a query signed xml message
     *
     * @param string $start must use format 'Y-m-d\TH:i:s'
     * @param string $end must use format 'Y-m-d\TH:i:s'
     * @param string $rfcType one of "RfcEmisor" or "RfcReceptor"
     * @param string $requestType one of "CFDI" or "metadata"
     * @return string
     */
    public function query(string $start, string $end, string $rfcType, string $requestType): string;

    /**
     * Creates a verify signed xml message
     *
     * @param string $requestId
     * @return string
     */
    public function verify(string $requestId): string;

    /**
     * Creates a download signed xml message
     *
     * @param string $packageId
     * @return string
     */
    public function download(string $packageId): string;
}
