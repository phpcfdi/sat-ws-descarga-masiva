<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use CfdiUtils\Certificado\SerialNumber;
use CfdiUtils\OpenSSL\OpenSSL;
use CfdiUtils\OpenSSL\OpenSSLPropertyTrait;
use RuntimeException;
use UnexpectedValueException;

/**
 * Class Certificado
 * @internal
 * @todo replace with cfdiutils once it can be created from contents and not from file
 */
class Certificado
{
    use OpenSSLPropertyTrait;

    /** @var string */
    private $rfc;

    /** @var string */
    private $certificateName;

    /** @var string */
    private $name;

    /** @var SerialNumber */
    private $serial;

    /** @var int */
    private $validFrom;

    /** @var int */
    private $validTo;

    /** @var string */
    private $pubkey;

    /** @var string */
    private $pemContents;

    /**
     * Certificado constructor.
     *
     * @param string $contents
     * @param OpenSSL $openSSL
     * @throws UnexpectedValueException when cannot read the certificate or is empty
     * @throws RuntimeException when cannot parse the certificate is empty
     * @throws RuntimeException when cannot get serialNumberHex or serialNumber from certificate
     */
    public function __construct(string $contents, OpenSSL $openSSL = null)
    {
        if ('' === $contents) {
            throw new UnexpectedValueException('Empty certificate contents|');
        }
        $this->setOpenSSL($openSSL ?: new OpenSSL());
        $contents = $this->obtainPemCertificate($contents);

        // get the certificate data
        $data = openssl_x509_parse($contents, true);
        if (! is_array($data)) {
            throw new RuntimeException('Cannot parse certificate contents');
        }

        // get the public key
        $pubKey = $this->obtainPubKeyFromContents($contents);

        // set all the values
        $this->certificateName = strval($data['name'] ?? '');
        $this->rfc = (string) strstr(($data['subject']['x500UniqueIdentifier'] ?? '') . ' ', ' ', true);
        $this->name = strval($data['subject']['name'] ?? '');
        $serial = new SerialNumber('');
        if (isset($data['serialNumberHex'])) {
            $serial->loadHexadecimal($data['serialNumberHex']);
        } elseif (isset($data['serialNumber'])) {
            $serial->loadDecimal($data['serialNumber']);
        } else {
            throw new RuntimeException('Cannot get serialNumberHex or serialNumber from certificate');
        }
        $this->serial = $serial;
        $this->validFrom = $data['validFrom_time_t'] ?? 0;
        $this->validTo = $data['validTo_time_t'] ?? 0;
        $this->pubkey = $pubKey;
        $this->pemContents = $contents;
    }

    private function obtainPemCertificate(string $contents): string
    {
        $openssl = $this->getOpenSSL();
        $extracted = $openssl->readPemContents($contents)->certificate();
        if ('' === $extracted) { // cannot extract, could be on DER format
            $extracted = $openssl->derCerConvertPhp($contents);
        }
        return $extracted;
    }

    /**
     * Check if this certificate belongs to a private key
     *
     * @param string $pemKeyFile
     * @param string $passPhrase
     *
     * @return bool
     *
     * @throws UnexpectedValueException if the file does not exists or is not readable
     * @throws UnexpectedValueException if the file is not a PEM private key
     * @throws RuntimeException if cannot open the private key file
     */
    public function belongsTo(string $pemKeyFile, string $passPhrase = ''): bool
    {
        $this->assertFileExists($pemKeyFile);
        $openSSL = $this->getOpenSSL();
        $keyContents = $openSSL->readPemContents(
            // intentionally silence this error, if return false then cast it to string
            strval(@file_get_contents($pemKeyFile))
        )->privateKey();
        if ('' === $keyContents) {
            throw new UnexpectedValueException("The file $pemKeyFile is not a PEM private key");
        }
        $privateKey = openssl_get_privatekey($keyContents, $passPhrase);
        if (false === $privateKey) {
            throw new RuntimeException("Cannot open the private key file $pemKeyFile");
        }
        $belongs = openssl_x509_check_private_key($this->getPemContents(), $privateKey);
        openssl_free_key($privateKey);
        return $belongs;
    }

    /**
     * RFC (Registro Federal de Contribuyentes) set when certificate was created
     * @return string
     */
    public function getRfc(): string
    {
        return $this->rfc;
    }

    public function getCertificateName(): string
    {
        return $this->certificateName;
    }

    /**
     * Name (Razón Social) set when certificate was created
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Certificate serial number as ASCII, this data is in the format required by CFDI
     * @return string
     */
    public function getSerial(): string
    {
        return $this->serial->asAscii();
    }

    public function getSerialObject(): SerialNumber
    {
        return clone $this->serial;
    }

    /**
     * Timestamp since the certificate is valid
     * @return int
     */
    public function getValidFrom(): int
    {
        return $this->validFrom;
    }

    /**
     * Timestamp until the certificate is valid
     * @return int
     */
    public function getValidTo(): int
    {
        return $this->validTo;
    }

    /**
     * String representation of the public key
     * @return string
     */
    public function getPubkey(): string
    {
        return $this->pubkey;
    }

    /**
     * The contents of the certificate in PEM format
     * @return string
     */
    public function getPemContents(): string
    {
        return $this->pemContents;
    }

    /**
     * Verify the signature of some data
     *
     * @param string $data
     * @param string $signature
     * @param int $algorithm
     *
     * @return bool
     *
     * @throws RuntimeException if cannot open the public key from certificate
     * @throws RuntimeException if openssl report an error
     */
    public function verify(string $data, string $signature, int $algorithm = OPENSSL_ALGO_SHA256): bool
    {
        $pubKey = openssl_get_publickey($this->getPubkey());
        if (false === $pubKey) {
            throw new RuntimeException('Cannot open public key from certificate');
        }
        try {
            $verify = openssl_verify($data, $signature, $pubKey, $algorithm);
            if (-1 === $verify) {
                throw new RuntimeException('OpenSSL Error: ' . openssl_error_string());
            }
        } finally {
            openssl_free_key($pubKey);
        }
        return (1 === $verify);
    }

    /**
     * @param string $filename
     * @throws UnexpectedValueException when the file does not exists or is not readable
     * @return void
     */
    protected function assertFileExists(string $filename): void
    {
        if (! file_exists($filename) || ! is_readable($filename) || is_dir($filename)) {
            throw new UnexpectedValueException("File $filename does not exists or is not readable");
        }
    }

    protected function obtainPubKeyFromContents(string $contents): string
    {
        try {
            $pubkey = openssl_get_publickey($contents);
            if (! is_resource($pubkey)) {
                return '';
            }
            $pubData = openssl_pkey_get_details($pubkey) ?: [];
            return $pubData['key'] ?? '';
        } finally {
            // close public key even if the flow is throw an exception
            if (is_resource($pubkey)) {
                openssl_free_key($pubkey);
            }
        }
    }
}
