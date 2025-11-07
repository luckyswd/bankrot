<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'public'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'yoda_style' => false,
        'cast_spaces' => ['space' => 'none'],
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_align' => false,
        'align_multiline_comment' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
