<?php

$baseDir    = dirname(__DIR__);
$currentDir = __DIR__;
echo <<<COMMENT
    /*
    |--------------------------------------------------------------------------
    | Poppy Framework cs Fixer
    |--------------------------------------------------------------------------
    | Document Url : https://github.com/FriendsOfPHP/PHP-CS-Fixer
    | Current Dir  : {$currentDir}
    */\n
    COMMENT;
if (false !== strpos($baseDir, 'vendor/poppy')) {
    $baseDir = dirname(__DIR__, 3);
    $folders = [
        $baseDir . '/config',
        $baseDir . '/modules',
    ];
}
else {
    $baseDir = dirname(__DIR__, 2);
    // for development
    $folders = [
        $baseDir . '/config',
        $baseDir . '/poppy',
        $baseDir . '/modules',
    ];
}

// 参考 Symfony\Component\Finder\Finder
$finder = PhpCsFixer\Finder::create()
    ->exclude('database')
    ->in($folders);
$config = new PhpCsFixer\Config();

return $config
    ->setUsingCache(true)
    ->setCacheFile($baseDir . '/storage/app/.php-cs-fixer.cache')
    ->setRiskyAllowed(false)
    ->setRules([
        '@auto'                                   => true,
        '@Symfony'                                => true,
        'declare_equal_normalize'                 => [
            'space' => 'single',
        ],
        'global_namespace_import'                 => [
            'import_classes' => true,
        ],
        'no_superfluous_phpdoc_tags'              => [
            'remove_inheritdoc' => false,
        ],
        'concat_space'                            => [
            'spacing' => 'one',
        ],
        'whitespace_after_comma_in_array'         => false,
        'class_attributes_separation'             => [
            'elements' => [
                'property' => 'one',
            ],
        ],
        'binary_operator_spaces'                  => [
            'operators' => [
                '=>' => 'align',
                '='  => 'align',
            ],
        ],
        'control_structure_continuation_position' => [
            'position' => 'next_line',
        ],
        'single_trait_insert_per_statement'       => false,
        'heredoc_indentation'                     => false,
        'function_declaration'                    => [
            'closure_fn_spacing' => 'none',
        ],
        'phpdoc_summary'                          => false,
        'trailing_comma_in_multiline'             => [
            'elements' => [],
        ],
    ])
    ->setFinder($finder);
