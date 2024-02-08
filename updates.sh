#!/usr/bin/env bash

# This script updates the composer.json file and stops on error.
# It first updates the composer dependencies to their latest versions that still satisfy the version constraints in the composer.json file.
# Then it upgrades the composer dependencies to their latest versions, ignoring the version constraints in the composer.json file.
# Finally, it bumps the version of the composer package.
# The -W (or --with-all-dependencies) option tells Composer to update not only the dependencies explicitly listed in the composer.json file, but also all of their dependencies.
# If any command fails, the script stops immediately.

composer update -W && composer upgrade -W && composer bump