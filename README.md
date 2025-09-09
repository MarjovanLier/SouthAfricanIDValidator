# South African ID Validator

## Table of Contents

- [Introduction](#introduction)
- [Badges](#badges)
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
    - [Getting Started](#getting-started)
- [Usage](#usage)
- [Troubleshooting](#troubleshooting)
- [Testing](#testing)
- [Contributing](#contributing)
- [License Information](#license-information)

## Introduction

The `SouthAfricanIDValidator` PHP package is your comprehensive solution for validating South African ID numbers,
crucial for any application processing or verifying South African identities. Validation of ID numbers is essential, as
it ensures that IDs adhere to both specific structural and contextual standards outlined in South African identity
documentation. This package stands out for its ease of use, high accuracy, and thorough validation checks, including
structure and format validation, birth date verification, gender, citizenship status, and race indicator digits, and
applying the Luhn algorithm for checksum digit validation.

## Badges

[![CI](https://github.com/MarjovanLier/SouthAfricanIDValidator/actions/workflows/ci.yml/badge.svg)](https://github.com/MarjovanLier/SouthAfricanIDValidator/actions/workflows/ci.yml)
[![OSV-Scanner](https://github.com/MarjovanLier/SouthAfricanIDValidator/actions/workflows/osv-scanner.yml/badge.svg)](https://github.com/MarjovanLier/SouthAfricanIDValidator/actions/workflows/osv-scanner.yml)
[![Packagist Version](https://img.shields.io/packagist/v/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Packagist Downloads](https://img.shields.io/packagist/dt/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Packagist License](https://img.shields.io/packagist/l/marjovanlier/southafricanidvalidator)](https://choosealicense.com/licenses/mit/)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Latest Stable Version](https://poser.pugx.org/marjovanlier/southafricanidvalidator/v/stable)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Phan Enabled](https://img.shields.io/badge/Phan-enabled-brightgreen.svg?style=flat)](https://github.com/phan/phan/)
[![Psalm Enabled](https://img.shields.io/badge/Psalm-enabled-brightgreen.svg?style=flat)](https://psalm.dev/)
[![codecov](https://codecov.io/github/MarjovanLier/SouthAfricanIDValidator/graph/badge.svg?token=bwkvkESlLe)](https://codecov.io/github/MarjovanLier/SouthAfricanIDValidator)

## Features

- **Comprehensive Structure Validation**: Validates the complete structure and format of South African ID numbers
- **Historical Date Support**: Correctly handles dates from the 1800s, 1900s, and 2000s with smart century determination
- **Birth Date Validation**: 
  - Validates encoded birth dates with proper calendar checks
  - Supports historical IDs (the 13-digit system was introduced in 1980-81 for all citizens)
  - Rejects dates more than 130 years in the past or in the future
- **Component Verification**:
  - Gender digit validation (positions 7-10)
  - Citizenship status validation (position 11: 0, 1, or 2)
  - Race indicator validation (position 12: must be 0-9)
- **Luhn Algorithm**: Applies the Luhn algorithm for checksum digit validation
- **Performance Optimised**: Includes early returns and efficient validation logic

## System Requirements

- PHP version 8.3 or higher.

## Installation

### Getting Started

To begin using the `SouthAfricanIDValidator` in your project, follow these steps:

1. **Install via Composer** - Run the following command to install the package:

   ```bash
   composer require marjovanlier/southafricanidvalidator
   ```

2. **Basic Usage Example** - After installation, South African ID numbers may be validated with the following
   code:

   ```php
   use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;

   // Instantiate the ID validator
   $validator = new SouthAfricanIDValidator();

   // Validate a South African ID number
   $idNumber = 'Your South African ID number here';
   $isValid = $validator->luhnIDValidate($idNumber);

   if ($isValid === true) {
       echo 'The ID number is valid.';
   } elseif ($isValid === false) {
       echo 'The ID number is invalid.';
   } else {
       echo 'The ID number does not meet specific constraints.';
   }
   ```

To integrate the `SouthAfricanIDValidator` into your project, install it via Composer:

```bash
composer require marjovanlier/southafricanidvalidator
```

## South African ID Number Format

A South African ID number is a 13-digit number with the format: **YYMMDDSSSSCAZ**

- **YYMMDD** (positions 1-6): Date of birth
  - YY can represent years from three centuries (1800s, 1900s, 2000s)
  - For example, "96" could mean 1896 or 1996, determined by age constraints
- **SSSS** (positions 7-10): Gender indicator
  - 0000-4999 for females
  - 5000-9999 for males
- **C** (position 11): Citizenship status
  - 0 = South African citizen
  - 1 = Permanent resident
  - 2 = Refugee
- **A** (position 12): Race indicator (deprecated since late 1980s, but must still be 0-9)
  - In the 1980s: 0=White, 1=Cape Coloured, 2=Malay, 3=Griqua, 4=Chinese, 5=Indian, 6=Other Asian, 7=Other Coloured
  - Post-1994: This digit was neutralised and no longer indicates race
- **Z** (position 13): Checksum digit (calculated using Luhn algorithm)

### Historical Context

The 13-digit ID number system was first introduced with the green bar-coded ID book in **1980**, replacing the older 9-digit system. The system was then legally codified by the Identification Act 72 of 1986.

#### Pre-1980 ID Format (Not supported by this validator)
The blue "Book of Life" (1972-1979) used a 9-digit number plus a race letter:
- **Format**: `YY DDD NNNN L` (e.g., `55 603 0142 W`)
- **YY**: Last two digits of birth year
- **DDD**: Census district code
- **NNNN**: Birth registration serial number
- **L**: Race classification letter:
  - **W** = White
  - **C** = Cape Coloured
  - **M** = Malay
  - **G** = Griqua
  - **H** = Chinese
  - **I** = Indian
  - **A** = Other Asian
  - **O** = Other Coloured
- **No checksum**: The Luhn algorithm was NOT used

Note: Black South Africans were issued separate "Reference Books" with different serial numbers and were not included in the blue Book of Life system.

#### Key Differences
- The old 9-digit system had **no Luhn checksum validation**
- Only stored the year of birth, not the full date
- Included geographic information (census district)
- Used a letter for race classification instead of a digit

When the 13-digit system was introduced in 1980, it was issued to all citizens including elderly adults, which is why valid IDs can contain birth dates from the 1800s.

## Usage

To validate a South African ID number, instantiate the `SouthAfricanIDValidator` class and call the `luhnIDValidate`
method:

```php
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;

$idNumber = 'Your South African ID number here';

$result = SouthAfricanIDValidator::luhnIDValidate($idNumber);

if ($result) {
    echo 'The ID number is valid.';
} elseif (!$result) {
    echo 'The ID number is invalid.';
} else {
    echo 'The ID number does not meet specific constraints.';
}
```

### Return Values

The `luhnIDValidate` method returns:
- `true`: The ID number is completely valid
- `false`: The ID has structural issues (wrong length, invalid date, invalid race indicator, or failed checksum)
- `null`: The citizenship status digit is invalid (not 0, 1, or 2). This special return value is maintained for backward compatibility.

## Troubleshooting

Should you encounter issues, the following solutions address common problems:

- **Problem:** Difficulty in installing the package via Composer.
    - **Solution:** Ensure your PHP version is at least 8.3 and that Composer is correctly installed on your system. For
      persistent issues, clear Composer's cache with `composer clear-cache` and attempt the installation again.

- **Problem:** Validation consistently results in `false`.
    - **Solution:** Ensure the ID number being validated conforms to the South African ID standard format of
      YYMMDDSSSSCAZ, consisting of exactly 13 digits.

- **Problem:** Validation returns `null` for certain IDs.
    - **Solution:** A `null` response typically indicates a problem with the citizenship status digit (the 11th
      character). This digit must be '0', '1', or '2' to represent a South African citizen, a permanent resident, or a
      refugee, respectively.

- **Problem:** Trying to validate a 9-digit ID from the old "Book of Life" system.
    - **Solution:** This validator only supports the 13-digit format introduced in 1980. The old 9-digit format
      (YY DDD NNNN + race letter) did not use Luhn validation and is not supported.

For additional assistance or to report an issue, please refer to the GitHub issue tracker.

## Testing

First, ensure you have all the necessary development dependencies installed by running `composer install --dev`.
Then, run the following command to execute the unit tests included with the package:

```bash
composer tests
```

## Contributing

We value your contributions to the SouthAfricanIDValidator package! Here's how you can help:

1. **Report Issues or Suggest Enhancements** - Noticed a bug or have an idea to make the package better? Start by filing
   an issue on our GitHub repo. This lets us discuss possible changes before you start coding.

2. **Contribute Code** - Fork the repo, then create a new branch for your work, adhering to our branching strategy
   guidelines. This keeps things organised and ensures consistency across contributions.

3. **Follow Our Coding Standards** - Write clean, readable code that adheres to the project coding standards. This ensures
   consistency and maintainability.

4. **Write or Update Tests** - Ensure reliability by writing tests for new features or updating existing tests where
   required.

5. **Submit a Pull Request** - Upon completion of changes, open a pull request against the main branch with a comprehensive
   explanation of the modifications and their rationale.

Contributions are essential to the continued improvement of the SouthAfricanIDValidator package.

## License Information

The SouthAfricanIDValidator is proudly open-sourced under the MIT Licence.
This licence allows you to use, modify, distribute and contribute back to the package, both in private and commercial
projects, but you must include the original copyright and license notice in any copy of the software.
For full details about your rights and obligations, refer to the [License File](LICENSE).

## Recent Improvements

The validator has been enhanced with the following improvements:

### Enhanced Validation
- **Historically Accurate**: Now correctly handles IDs from citizens born in the 1800s (based on research showing the 13-digit ID system was introduced in 1980 with the green ID book)
- **Smart Century Detection**: Automatically determines the correct century (1800s, 1900s, or 2000s) based on age constraints
- **Age Validation**: Rejects IDs representing ages over 130 years
- **Future Date Protection**: Rejects IDs with birth dates in the future
- **Race Indicator Validation**: Now validates that position 12 contains a digit (0-9)

### Code Quality
- Improved documentation with clear explanations of all return values
- Performance optimisations with early returns
- Full backward compatibility maintained

## Release Notes

Please refer to the [GitHub Releases page](https://github.com/MarjovanLier/SouthAfricanIDValidator/releases) for the latest updates.