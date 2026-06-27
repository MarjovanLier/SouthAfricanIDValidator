<?php

declare(strict_types=1);

/*
 * PHPUnit bootstrap that registers the minimum coverage threshold.
 *
 * The robiningelbrecht/phpunit-coverage-tools extension reads the
 * --min-coverage option from $_SERVER['argv'] when the test run finishes.
 * Passing it as a PHPUnit CLI option is rejected, and the historic
 * "-d --min-coverage=100" workaround now raises a test-runner warning under
 * PHPUnit 13 (which fails the suite via failOnWarning). Appending the option
 * here keeps it out of PHPUnit's option parser whilst the extension still
 * reads it from argv.
 */
$argv = $_SERVER['argv'] ?? [];

if (is_array($argv)) {
    $argv[] = '--min-coverage=100';
    $_SERVER['argv'] = $argv;
}

require __DIR__ . '/../vendor/autoload.php';
