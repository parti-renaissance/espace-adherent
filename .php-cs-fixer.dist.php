<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->in(__DIR__.'/config')
    ->in(__DIR__.'/migrations')
    ->in(__DIR__.'/features')
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,
        '@Symfony' => true,
        '@DoctrineAnnotation' => true,
        'phpdoc_summary' => false,
        'no_unneeded_final_method' => false,
        'declare_strict_types' => false,
        'use_arrow_functions' => false,
        'void_return' => false,
        'phpdoc_separation' => false,
        'no_superfluous_phpdoc_tags' => true,
        'concat_space' => ['spacing' => 'none'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'phpdoc_to_comment' => false,
        'native_constant_invocation' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'attribute_empty_parentheses' => true,
        'ordered_attributes' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php_cs/.php_cs.cache')
;
