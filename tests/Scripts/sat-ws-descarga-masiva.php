<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts;

use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions\Argument;
use PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions\Arguments;
use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;
use Throwable;

require_once __DIR__ . '/../../vendor/autoload.php';

exit(call_user_func(
    function (string $action, string ...$parameters): int {
        $printer = new Actions\Printer();
        $arguments = new Arguments(
            new Argument('o', 'output', 'output directory to write request and responses'),
            new Argument('env', 'environment', 'environment file, default to working-directory/.env')
        );
        if (in_array($action, ['-h', '--help'], true)) {
            $action = 'help';
        }
        ['matched' => $matched, 'unmatched' => $unmatched] = $arguments->parseParameters($parameters);
        $envFile = strval($matched['env'] ?? '') ?: './.env';
        if (file_exists($envFile)) {
            $dotEnv = new Dotenv();
            $dotEnv->load($envFile);
        }
        $outputDirectory = strval($matched['o'] ?? '');
        try {
            $askForHelp = (in_array('-h', $unmatched, true) || in_array('--help', $unmatched, true));
            $fielData = new Actions\FielData(
                strval(getenv('WSDM_CERTIFICATE') ?? ''),
                strval(getenv('WSDM_PRIVATEKEY') ?? ''),
                strval(getenv('WSDM_PASSPHRASE') ?? '')
            );
            $actionClass = __NAMESPACE__ . '\Actions\\' . ucfirst($action);
            if (! class_exists($actionClass)) {
                throw new RuntimeException("Action $action not found");
            }
            /** @var Actions\ActionInterface $actionObject */
            $actionObject = new $actionClass($fielData, $printer, $outputDirectory);
            if ($askForHelp) {
                $actionObject->help();
            } else {
                $actionObject->run(...$unmatched);
            }
            return 0;
        } catch (Throwable $exception) {
            $printer->stderr("ERROR: {$exception->getMessage()}");
            return 1;
        }
    },
    $argv[1] ?? '' ?: 'help',
    ...(array_slice($argv, 2) ?? [])
));
