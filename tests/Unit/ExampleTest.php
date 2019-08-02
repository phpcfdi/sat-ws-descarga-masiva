<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit;

use PhpCfdi\SatWsDescargaMasiva\DateTime;
use PhpCfdi\SatWsDescargaMasiva\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\DownloadRequestQuery;
use PhpCfdi\SatWsDescargaMasiva\Enums\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Enums\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Fiel;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Tests\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testAuthenticationUsingFakeFiel(): void
    {
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            trim($this->fileContents('fake-fiel/password.txt'))
        );
        $webclient = new GuzzleWebClient();

        $service = new Service($fiel, $webclient);
        $token = $service->authenticate();
        $this->assertTrue($token->isValid());
    }

    public function testDownloadRequestUsingFakeFiel(): void
    {
        $fiel = new Fiel(
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL_password.key.pem'),
            $this->fileContents('fake-fiel/aaa010101aaa_FIEL.cer'),
            trim($this->fileContents('fake-fiel/password.txt'))
        );
        $webclient = new GuzzleWebClient();
        $dateTimePeriod = new DateTimePeriod(new DateTime('2019-01-01 00:00:00'), new DateTime('2019-01-01 00:04:00'));
        $rfc = 'aaa010101aaa';
        $downloadRequestQuery = new DownloadRequestQuery($dateTimePeriod, $rfc, DownloadType::received(), RequestType::cfdi());

        $service = new Service($fiel, $webclient);
        $downloadRequestResult = $service->downloadRequest($downloadRequestQuery);
        $this->assertTrue($downloadRequestResult->isAccepted());
    }
}
