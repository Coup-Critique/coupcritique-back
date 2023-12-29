<?php

declare(strict_types=1);

// Create finder in order to exclude directories in PHPCSFixer's checks
    // @phpstan-ignore-next-line
$finder = PhpCsFixer\Finder::create()
    ->exclude(['app', 'bin', 'build', 'var', 'vendor', 'public', 'node_modules'])
    ->in(__DIR__);

// @phpstan-ignore-next-line
$config = new PhpCsFixer\Config();

// @phpstan-ignore-next-line
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        // For more info : https://cs.symfony.com/doc/rules/index.html
        '@PSR2' => true,
        '@Symfony' => true, 
        '@Symfony:risky' => true, 
        'declare_strict_types' => true, 
        'no_useless_else' => true, 
        'no_useless_return' => true,
        'combine_consecutive_unsets' => true,
        'list_syntax' => ['syntax' => 'short'],
        'increment_style' => ['style' => 'post'],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => true,
        'single_line_throw' => false,
        'ordered_class_elements' => true,
        'class_attributes_separation' => [
            'elements' => [
                // Don't specify the "const" key as we want custom spacing between constants.
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'operator_linebreak' => true,
        'phpdoc_line_span' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'visibility_required' => true,
        // Prevent deletion of Doctrine Annotation used on multiple lines like Assert\Expression
        // Remove once https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/5749 is fixed
        'phpdoc_trim_consecutive_blank_line_separation' => false,
        'phpdoc_separation' => false,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => true,
            'remove_inheritdoc' => true
        ],
        'modernize_strpos' => true,
    ])
    ->setFinder($finder);