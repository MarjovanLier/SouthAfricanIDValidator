<?php

/**
 * @noinspection DevelopmentDependenciesUsageInspection
 */

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->bootstrapFiles([__DIR__ . '/vendor/autoload.php']);

    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);

    $rectorConfig->skip([__DIR__ . '/bootstrap/cache']);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rule(RenameForeachValueVariableToMatchExprVariableRector::class);
    $rectorConfig->rule(RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class);
    $rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);
    $rectorConfig->rule(NullableCompareToNullRector::class);
    $rectorConfig->rule(EncapsedStringsToSprintfRector::class);
    $rectorConfig->rule(NewlineAfterStatementRector::class);
    $rectorConfig->rule(NewlineBeforeNewAssignSetRector::class);
    $rectorConfig->rule(PostIncDecToPreIncDecRector::class);
    $rectorConfig->rule(SeparateMultiUseImportsRector::class);
    $rectorConfig->rule(SplitDoubleAssignRector::class);

    $rectorConfig->phpVersion(PhpVersion::PHP_83);

    // define sets of rules
    $rectorConfig->sets(
        [
            LevelSetList::UP_TO_PHP_83,
            SetList::CODE_QUALITY,
            SetList::CODING_STYLE,
            SetList::DEAD_CODE,
            SetList::EARLY_RETURN,
            SetList::PHP_83,
            SetList::TYPE_DECLARATION,
            SetList::NAMING,
            SetList::PRIVATIZATION,
            SetList::STRICT_BOOLEANS,
            SetList::INSTANCEOF,
        ],
    );

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    $rectorConfig->skip(
        [
            FlipTypeControlToUseExclusiveTypeRector::class,
        ],
    );
};
