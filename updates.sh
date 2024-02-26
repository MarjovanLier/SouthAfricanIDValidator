#!/usr/bin/env bash

# This script updates the composer.json file and halts on error.
# It first updates the composer dependencies to their latest versions that still satisfy the version constraints in the composer.json file.
# Then it upgrades the composer dependencies to their latest versions, disregarding the version constraints in the composer.json file.
# Finally, it increments the version of the composer package.
# The -W (or --with-all-dependencies) option informs Composer to update not only the dependencies explicitly listed in the composer.json file, but also all of their dependencies.
# If any command fails, the script halts immediately.

composer update -W && composer upgrade -W && composer bump