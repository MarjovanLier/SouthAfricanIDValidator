<?php

/**
 * @noinspection DevelopmentDependenciesUsageInspection
 */

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    // Autoload and paths configuration
    ->withBootstrapFiles([__DIR__ . '/vendor/autoload.php'])
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSkip([
        __DIR__ . '/bootstrap/cache',
        RemoveConcatAutocastRector::class,
    ])

    // PHP version
    ->withPhpVersion(PhpVersion::PHP_83)

    // Rule sets - removing duplicates (PHP_83 is included in UP_TO_PHP_83)
    // Rector 2.9 folded EARLY_RETURN, INSTANCEOF and strict types into these sets and dropped NAMING.
    ->withSets([
        LevelSetList::UP_TO_PHP_83,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
    ])

    // Import configuration
    ->withImportNames(importShortClasses: false)

    // Performance optimisations
    ->withParallel()
    ->withCache(
        cacheDirectory: __DIR__ . '/.rector-cache',
        cacheClass: FileCacheStorage::class,
    );
