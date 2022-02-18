<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 * @see https://cs.symfony.com/doc/ruleSets/
 * @see https://cs.symfony.com/doc/rules/
 */

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/build/php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@PHP71Migration:risky' => true,
        '@PHP73Migration' => true,
        // PSR12 (remove when php-cs-fixer reaches ^3.1.1)
        'class_definition' => ['space_before_parenthesis' => true],
        // symfony
        // 'class_attributes_separation' => true, // conflict with PSR12
        'whitespace_after_comma_in_array' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'function_typehint_space' => true,
        'no_blank_lines_after_phpdoc' => true,
        'object_operator_without_whitespace' => true,
        'binary_operator_spaces' => true,
        'phpdoc_scalar' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'single_quote' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_unused_imports' => true,
        'yoda_style' => ['equal' => true, 'identical' => true, 'less_and_greater' => null],
        'standardize_not_equals' => true,
        'concat_space' => ['spacing' => 'one'],
        'linebreak_after_opening_tag' => true,
        // symfony:risky
        'no_alias_functions' => true,
        'self_accessor' => true,
        // contrib
        'not_operator_with_successor_space' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->append([__FILE__])
            ->exclude(['vendor', 'build'])
    )
;
