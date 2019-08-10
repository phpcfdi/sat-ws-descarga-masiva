<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts;

use RuntimeException;
use Throwable;

require_once __DIR__ . '/../../vendor/autoload.php';

exit(call_user_func(
    function (string $action, string ...$parameters): int {
        $printer = new CLI\Printer();
        $arguments = new CLI\Arguments(
            new CLI\Argument('o', 'output', 'output directory to write request and responses'),
            new CLI\Argument('c', 'certificate', 'certificate file'),
            new CLI\Argument('k', 'private-key', 'private key file in pem format'),
            new CLI\Argument('p', 'pass-phrase', 'pass phrase to open private key')
        );
        if (in_array($action, ['-h', '--help'], true)) {
            $action = 'help';
        }
        ['matched' => $matched, 'unmatched' => $unmatched] = $arguments->parseParameters($parameters);
        $outputDirectory = strval($matched['o'] ?? '');
        try {
            $askForHelp = (in_array('-h', $unmatched, true) || in_array('--help', $unmatched, true));
            $fielData = new Helpers\FielData(
                strval($matched['c'] ?? ''),
                strval($matched['k'] ?? ''),
                strval($matched['p'] ?? '')
            );
            $actionClass = __NAMESPACE__ . '\Actions\\' . ucfirst($action);
            if (! class_exists($actionClass)) {
                throw new RuntimeException("Action $action not found");
            }
            /** @var CLI\ActionInterface $actionObject */
            $actionObject = new $actionClass($fielData, $printer, $outputDirectory);
            if ($askForHelp) {
                $actionObject->runHelp();
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
