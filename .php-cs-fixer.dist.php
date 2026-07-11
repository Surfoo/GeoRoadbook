<?php

$finder = (new PhpCsFixer\Finder())
    ->in('src')
    ->in('tests')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12'                 => true,
        '@Symfony'               => true,
        'binary_operator_spaces' => ['default' => 'align_single_space_minimal'],
        'concat_space'           => ['spacing' => 'one'],
        'yoda_style'             => false,
    ])
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder($finder)
;
