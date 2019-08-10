<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\AbstractAction;
use RuntimeException;
use Throwable;

class Credentials extends AbstractAction
{
    public function run(string ...$parameters): void
    {
        $fielData = $this->getFielData();
        $passPhraseLength = mb_strlen($fielData->getPassPhrase());
        $this->stdout(...[
            'Certificate: ' . $fielData->getCertificateFile(),
            'Private key: ' . $fielData->getPrivateKeyFile(),
            'Pass phrase: ' . (($passPhraseLength > 0) ? str_repeat('*', $passPhraseLength) : '(none)'),
        ]);
        try {
            $fiel = $fielData->createFiel();
        } catch (Throwable $exception) {
            throw new RuntimeException('Unable to create fiel from current data', 0, $exception);
        }
        $fielIsValid = $fiel->isValid();
        $this->stdout(...[
            'Valid: ' . (($fielIsValid) ? 'yes' : 'no'),
            'RFC: ' . $fiel->getRfc(),
        ]);
        if (! $fielIsValid) {
            throw new RuntimeException('Fiel is not valid!');
        }
    }

    public function runHelp(): void
    {
        $this->stdout('return information about current credentials');
    }
}
