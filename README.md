# South African ID Validator

This PHP package validates South African ID numbers by checking their structural and contextual rules.

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