<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

$fileHeaderComment = <<<'COMMENT'
    This file is part of the Starfleet Project.

    (c) Starfleet <msantostefano@jolicode.com>

    For the full copyright and license information,
    please view the LICENSE file that was distributed with this source code.
    COMMENT;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->append([
        __FILE__,
    ])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP80Migration' => true,
        '@PhpCsFixer' => true,
        'php_unit_internal_class' => false, // From @PhpCsFixer but we don't want it
        'php_unit_test_class_requires_covers' => false, // From @PhpCsFixer but we don't want it
        'phpdoc_add_missing_param_annotation' => false, // From @PhpCsFixer but we don't want it
        'header_comment' => ['header' => $fileHeaderComment, 'separate' => 'both'],
        'concat_space' => ['spacing' => 'one'],
    ])
    ->setFinder($finder)
;
