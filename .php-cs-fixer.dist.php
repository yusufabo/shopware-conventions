<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/custom/plugins/Conventions/src')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;
