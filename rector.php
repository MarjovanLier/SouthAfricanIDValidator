<?php

/**
 * @noinspection DevelopmentDependenciesUsageInspection
 */

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    // Autoload and paths configuration
    ->withBootstrapFiles([__DIR__ . '/vendor/autoload.php'])
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSkip([
        __DIR__ . '/bootstrap/cache',
        FlipTypeControlToUseExclusiveTypeRector::class,
        RenameForeachValueVariableToMatchExprVariableRector::class => [
            __DIR__ . '/tests/Feature/ComplianceValidationTest.php',
        ],
        RemoveConcatAutocastRector::class,
    ])

    // PHP version
    ->withPhpVersion(PhpVersion::PHP_83)

    // Rule sets - removing duplicates (PHP_83 is included in UP_TO_PHP_83)
    ->withSets([
        LevelSetList::UP_TO_PHP_83,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        SetList::NAMING,
        SetList::PRIVATIZATION,
        SetList::STRICT_BOOLEANS,
        SetList::INSTANCEOF,
    ])

    // Import configuration
    ->withImportNames(importShortClasses: false)

    // Additional specific rules not in standard sets
    ->withRules([
        // Strict Types - Ensure all files have declare strict (not in standard sets)
        DeclareStrictTypesRector::class,
    ])

    // Performance optimisations
    ->withParallel()
    ->withCache(
        cacheDirectory: __DIR__ . '/.rector-cache',
        cacheClass: FileCacheStorage::class,
    );
