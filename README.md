# South African ID Validator

The `SouthAfricanIDValidator` PHP package is your comprehensive solution for validating South African ID numbers, crucial for any application processing or verifying South African identities. Validation of ID numbers is essential, as it ensures that IDs adhere to both specific structural and contextual standards outlined in South African identity documentation. This package stands out for its ease of use, high accuracy, and thorough validation checks, including structure and format validation, birth date verification, gender, citizenship status, and race indicator digits, and applying the Luhn algorithm for checksum digit validation.

## Badges

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

- Validates the structure and format of South African ID numbers.
- Checks the validity of the birth date encoded within the ID number.
- Verifies gender, citizenship status, and race indicator digits.
- Applies the Luhn algorithm to validate the checksum digit.

## System Requirements

- PHP version 8.2 or higher.

## Installation

### Getting Started

To begin using the `SouthAfricanIDValidator` in your project, follow these simple steps:

1. **Install via Composer** - Run the following command to install the package:

   ```bash
   composer require marjovanlier/southafricanidvalidator
   ```

2. **Basic Usage Example** - After installation, you can start validating South African ID numbers with the following code snippet:

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

## Usage

To validate a South African ID number, instantiate the `SouthAfricanIDValidator` class and call the `luhnIDValidate`
method:

```php
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;

$idNumber = 'Your South African ID number here';

## Troubleshooting

Encountering issues? Here are solutions to some common problems:

- **Problem:** Difficulty in installing the package via Composer.
   - **Solution:** Verify your PHP version is at least 8.2 and that Composer is correctly installed on your system. For persistent issues, try clearing Composer's cache with `composer clear-cache` and attempt the installation again.

- **Problem:** Validation consistently results in `false`.
   - **Solution:** Ensure the ID number being validated conforms to the South African ID standard format of YYMMDDSSSSCAZ, consisting of exactly 13 digits.

- **Problem:** Validation returns `null` for certain IDs.
   - **Solution:** A `null` response typically indicates a problem with the citizenship status digit (the 11th character). This digit must be '0', '1', or '2' to represent a South African citizen, a permanent resident, or a refugee, respectively.

For additional help or to report a new issue, please visit our GitHub issue tracker.

$result = SouthAfricanIDValidator::luhnIDValidate($idNumber);

if ($result) {
    echo 'The ID number is valid.';
} elseif (!$result) {
    echo 'The ID number is invalid.';
} else {
    echo 'The ID number does not meet specific constraints.';
}
```

The `luhnIDValidate` method returns `true` if the ID number is valid, `false` if it's invalid, and `null` if specific
criteria aren't met (e.g., incorrect citizenship status digit).

## Testing

Run the following command to execute the unit tests included with the package:

```bash
composer tests
```

## Contributing

Contributions to the SouthAfricanIDValidator package are welcome. Please follow these guidelines:

1. Open an issue to discuss proposed changes.
2. Fork the repository and create a new branch for your feature or fix.
3. Write clean code and adhere to the existing coding standards.
4. Add or update tests to cover your changes.
5. Submit a pull request with a clear description of your modifications.

## Contributing

We value your contributions to the SouthAfricanIDValidator package! Here's how you can help:

1. **Report Issues or Suggest Enhancements** - Noticed a bug or have an idea to make the package better? Start by filing an issue on our GitHub repo. This lets us discuss possible changes before you start coding.

2. **Contribute Code** - Fork the repo, then create a new branch for your work. This keeps things organized.

3. **Follow Our Coding Standards** - Write clean, readable code that adheres to our coding standards. This makes it easier for everyone to understand and maintain.

4. **Write or Update Tests** - Help ensure reliability by writing tests for new features or updating existing tests as needed.

5. **Submit a Pull Request** - Finished making changes? Open a pull request against the main branch with a detailed explanation of what you did and why.

Your contributions are crucial to improving the SouthAfricanIDValidator package for everyone!

## License Information

The SouthAfricanIDValidator is proudly open-sourced under the MIT License, providing you with the freedom to use, modify, distribute, and contribute back to the package while following the terms of the license. For full details about your rights and obligations, refer to the [License File](LICENSE).

## Changelog and Versioning

Keeping track of changes is important. Our changelog documents all significant updates, ensuring you're always informed about new features and fixes. We follow [Semantic Versioning](https://semver.org/) to maintain compatibility and stability across releases.

Stay updated by checking our [GitHub Releases page](https://github.com/MarjovanLier/SouthAfricanIDValidator/releases).