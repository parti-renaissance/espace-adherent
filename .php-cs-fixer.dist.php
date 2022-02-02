<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->in(__DIR__.'/config')
    ->in(__DIR__.'/migrations')
    ->in(__DIR__.'/features')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@DoctrineAnnotation' => true,
        'phpdoc_summary' => false,
        'no_unneeded_final_method' => false,
        'no_superfluous_phpdoc_tags' => true,
        'concat_space' => ['spacing' => 'none'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'phpdoc_to_comment' => false,
        'native_constant_invocation' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'PedroTroller/line_break_between_method_arguments' => [ 'max-args' => 20 ],
        'PedroTroller/line_break_between_statements' => true,
        'App/doctrine_migration_clean' => true,
        'App/sensio_to_symfony_route' => true,
        'App/method_to_route_annotation' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php_cs.cache')
    ->registerCustomFixers([
        new PedroTroller\CS\Fixer\CodingStyle\LineBreakBetweenMethodArgumentsFixer,
        new PedroTroller\CS\Fixer\CodingStyle\LineBreakBetweenStatementsFixer,
        new App\Fixer\DoctrineMigrationCleanFixer,
        new App\Fixer\SensioToSymfonyRouteFixer,
        new App\Fixer\MethodToRouteAnnotationFixer,
    ])
;
