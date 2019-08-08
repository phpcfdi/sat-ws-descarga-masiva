<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI\AbstractAction;

class Help extends AbstractAction
{
    public function run(string ...$parameters): void
    {
        $this->stdout(...[
            'Use this tool to run command using credentials defined in environment',
            'Commands:',
            '  help: This command',
            '  credentials: Show credential information from environment',
            '    WSDM_CERTIFICATE path to certificate',
            '    WSDM_PRIVATEKEY path to PEM private key',
            '    WSDM_PASSPHRASE pass phrase to open private key',
            '  request: Perform a request',
        ]);
    }

    public function help(): void
    {
        $this->run();
    }
}
