# South African ID Validator

The `SouthAfricanIDValidator` PHP package offers a comprehensive solution for validating South African ID numbers. It
ensures that ID numbers conform to the specific structural and contextual standards as outlined by South African
identity documentation requirements.

## Badges

[![Packagist Version](https://img.shields.io/packagist/v/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Packagist Downloads](https://img.shields.io/packagist/dt/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Packagist License](https://img.shields.io/packagist/l/marjovanlier/southafricanidvalidator)](https://choosealicense.com/licenses/mit/)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Latest Stable Version](https://poser.pugx.org/marjovanlier/southafricanidvalidator/v/stable)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Phan Enabled](https://img.shields.io/badge/Phan-enabled-brightgreen.svg?style=flat)](https://github.com/phan/phan/)
[![Psalm Enabled](https://img.shields.io/badge/Psalm-enabled-brightgreen.svg?style=flat)](https://psalm.dev/)

## Features

- Validates the structure and format of South African ID numbers.
- Checks the validity of the birth date encoded within the ID number.
- Verifies gender, citizenship status, and race indicator digits.
- Applies the Luhn algorithm to validate the checksum digit.

## System Requirements

- PHP version 8.2 or higher.

## Installation

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

## License

This project is open-sourced under the MIT License. See the [License File](LICENSE) for more information.