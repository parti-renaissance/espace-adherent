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
        '@PHP8x3Migration' => true,
        // '@PHP8x4Migration' => true, // pass to true when phpstan is v2
        '@Symfony' => true,
        'phpdoc_summary' => false,
        'no_unneeded_final_method' => false,
        'declare_strict_types' => true,
        'use_arrow_functions' => false,
        'no_superfluous_phpdoc_tags' => true,
        'concat_space' => ['spacing' => 'none'],
        'phpdoc_to_comment' => false,
        'native_constant_invocation' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'attribute_empty_parentheses' => true,
        'ordered_attributes' => true,
        'no_unused_imports' => true,
        'nullable_type_declaration_for_default_null_value' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php_cs/.php_cs.cache')
;
