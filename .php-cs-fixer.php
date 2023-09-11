<?php
$finder = new PhpCsFixer\Finder();
$finder
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);
;
return (new PhpCsFixer\Config())
    //->setUsingCache(false)
    ->setRiskyAllowed(true)
    //->setIndent("    ")
    //->setLineEnding("CRLF")
    //->setLineEnding("\r\n")
    //->setLineEnding(PHP_EOL)
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/cache/.php_cs.cache')
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'align_multiline_comment'               => [
            'comment_type' => 'phpdocs_like',
        ],
        'array_indentation'                     => true,
        'array_syntax'                          => [
            'syntax' => 'short'
        ],
        'binary_operator_spaces'                => [
            'default' => 'align'
        ],
        'blank_line_after_namespace'            => true,
        'blank_line_after_opening_tag'          => true,
        'blank_line_before_statement'           => false,
        'concat_space'                          => [
            'spacing' => 'one'
        ],
        'linebreak_after_opening_tag'           => true,
        'mb_str_functions'                      => true,
        'no_php4_constructor'                   => true,
        'native_function_invocation'            => true,
        'no_superfluous_phpdoc_tags'            => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else'                       => true,
        'no_useless_return'                     => true,
        'ordered_imports'                       => true,
        'php_unit_strict'                       => true,
        'phpdoc_order'                          => true,
        'semicolon_after_instruction'           => true,
        'strict_comparison'                     => true,
        'strict_param'                          => true,
    ])
    ;