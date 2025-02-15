<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\CodingStandard\CodeIgniter4;
use Nexus\CsConfig\Factory;
use Nexus\CsConfig\Fixer\Comment\NoCodeSeparatorCommentFixer;
use Nexus\CsConfig\FixerGenerator;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/user_guide_src/source',
    ])
    ->notPath([
        'ci3sample/',
        'database/query_builder/075.php',
        'libraries/sessions/016.php',
        'outgoing/response/031.php',
        'outgoing/response/032.php',
    ]);

$overrides = [
    'echo_tag_syntax'              => false,
    'php_unit_internal_class'      => false,
    'no_unused_imports'            => false,
    'class_attributes_separation'  => false,
    'fully_qualified_strict_types' => [
        'import_symbols'                        => false,
        'leading_backslash_in_global_namespace' => true,
    ],
    // for updating to coding-standard
    'modernize_strpos'    => true,
    'php_unit_attributes' => true,
];

$options = [
    'cacheFile'    => 'build/.php-cs-fixer.user-guide.cache',
    'finder'       => $finder,
    'customFixers' => FixerGenerator::create('vendor/nexusphp/cs-config/src/Fixer', 'Nexus\\CsConfig\\Fixer'),
    'customRules'  => [
        NoCodeSeparatorCommentFixer::name() => true,
    ],
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forProjects();
