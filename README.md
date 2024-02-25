# South African ID Validator

The `SouthAfricanIDValidator` PHP package offers a comprehensive solution for validating South African ID numbers, ensuring they conform to specific structural and contextual standards as outlined by South African identity documentation. ID number validation involves checking the accuracy of the number against these standards, which is critical for applications dealing with South African identity verification. Key features of this package include ease of use, high accuracy, and thorough validation checks for a reliable identification process.

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

## Getting Started

To get started with the `SouthAfricanIDValidator`, follow these steps:

### Installation

To integrate the `SouthAfricanIDValidator` into your project, install it via Composer:

```bash
composer require marjovanlier/southafricanidvalidator
```

### Basic Usage Example

To validate a South African ID number, instantiate the `SouthAfricanIDValidator` class and call the `luhnIDValidate` method:

```php
use MarjovanLier\\"},{

To integrate the `SouthAfricanIDValidator` into your project, install it via Composer:

```bash
composer require marjovanlier/southafricanidvalidator
```

SouthAfricanIDValidator\SouthAfricanIDValidator;

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
This example demonstrates how to validate a South African ID number. The `luhnIDValidate` method will return `true` if the ID number is valid, `false` if it's invalid, and `null` if specific criteria aren't met (e.g., incorrect citizenship status digit).

## Detailed Usage Guide

Further explore the package's capabilities with comprehensive examples and detailed explanation of the validation process output, including different scenarios and validation status.

### Configuration Options (if applicable)

Document any user-adjustable settings here.

## Troubleshooting

Address common issues or questions with solutions or workarounds.

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

The `luhnIDValidate` method returns `true` if the ID number is valid, `false` if it's invalid, and `null` if specific
criteria aren't met (e.g., incorrect citizenship status digit).

## Testing

Run the following command to execute the unit tests included with the package:

```bash
composer tests
```

## Contributing

We welcome community contributions to improve the `SouthAfricanIDValidator` package. Here's how you can contribute:

1. Report bugs or suggest enhancements by opening an issue.
2. Fork the repository and create a new branch for your feature or fix.

Contributions to the SouthAfricanIDValidator package are welcome. Please follow these guidelines:

1. Open an issue to discuss proposed changes.
2. Fork the repository and create a new branch for your feature or fix.
3. Write clean code and adhere to the existing coding standards.
4. Add or update tests to cover your changes.
5. Submit a pull request with a clear description of your modifications.

## Changelog and Versioning

Keep track of changes and updates with our changelog. Learn about our versioning scheme to stay up-to-date with the latest versions.

## License Information

This project is open-sourced under the MIT License. Users are granted extensive rights to use, modify, and distribute the software, provided they comply with the license terms. See the [License File](LICENSE) for more information.

## Contact/Support Information

For support or to engage with our community, contact us at [support@email.com] or join our community forum.

This project is open-sourced under the MIT License. See the [License File](LICENSE) for more information.