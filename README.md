# South African ID Validator

This PHP package validates South African ID numbers by checking their structural and contextual rules.

[![Packagist Version](https://img.shields.io/packagist/v/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Packagist Downloads](https://img.shields.io/packagist/dt/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Packagist License](https://img.shields.io/packagist/l/marjovanlier/southafricanidvalidator)](https://choosealicense.com/licenses/mit/)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/marjovanlier/southafricanidvalidator)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![Latest Stable](https://poser.pugx.org/marjovanlier/southafricanidvalidator/v/stable)](https://packagist.org/packages/marjovanlier/southafricanidvalidator)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Phan Enabled](https://img.shields.io/badge/Phan-enabled-brightgreen.svg?style=flat)](https://github.com/phan/phan/)
[![Psalm Enabled](https://img.shields.io/badge/Psalm-enabled-brightgreen.svg?style=flat)](https://psalm.dev/)

## Requirements

- PHP 8.2 or higher.

## Installation

Install the package using [Composer](https://getcomposer.org/):

```bash
composer require marjovanlier/southafricanidvalidator
```

## Usage

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

## Testing

Run the package tests with:

```bash
composer test
```

## Contributing

Contributions are welcome! For significant changes, please open an issue first to discuss your ideas. Ensure that you
update tests as appropriate.

Please make sure to update tests as appropriate.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.