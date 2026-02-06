# South African ID Validator

## Project Overview

PHP library for validating 13-digit South African identity numbers (YYMMDDSSSSCAZ).
Validates date, gender, citizenship, race indicator, and Luhn checksum.

- **Namespace**: `MarjovanLier\SouthAfricanIDValidator`
- **PHP**: >=8.3 (tested on 8.3, 8.4)
- **Production dependency**: `marjovanlier/stringmanipulation`

## Architecture

- `src/SouthAfricanIDValidator.php` — Final class, all static methods
- `src/DTO/` — Readonly DTOs with ArrayAccess for backwards compatibility
- `src/Enum/` — PHP 8.1 backed string enums (Gender, Citizenship, RaceIndicator)

All public methods are static. DTOs are immutable (throw BadMethodCallException on mutation).
Enums use static factory methods (fromDigit, fromSequenceNumber).

## Quality Standards

- **100% line coverage** enforced by PHPUnit config (non-negotiable)
- **90% mutation score** enforced by Infection
- **PHPStan level max** with strict rules and bleeding edge
- **Phan strict mode** with 12 plugins
- **PER 3.0 coding style** enforced by Laravel Pint
- **Rector** for automated refactoring checks (dry-run)
- Suppression is a cop-out — fix issues, do not suppress them

## Commands

```bash
composer tests                  # Run ALL quality checks (required before commit)
composer test:phpunit           # Unit tests + 100% coverage
composer test:infection         # Mutation testing (90% MSI)
composer test:phpstan           # Static analysis (level max)
composer test:phan              # AST-based static analysis
composer test:code-style        # Pint (PER 3.0)
composer test:rector            # Refactoring check (dry-run)
composer test:phpmd             # Code smell detection
composer test:lint              # PHP syntax check
composer test:vulnerabilities   # Dependency security scan
composer test:composer-validate # Composer validation
```

## Development Conventions

- All files must have `declare(strict_types=1)`
- Use early returns for validation logic
- Prefer readonly DTOs for complex return values
- Prefer enums over constants for type safety
- Use comprehensive data providers in tests with descriptive names
- Write mutation-resistant tests — avoid trivial assertions
- Use `last_commit` bash command after each git commit
- Always use South African English without slang
- Always ask before making config file changes

## Testing Patterns

- Tests mirror the static method structure of the main class
- Data providers return typed arrays with descriptive keys
- `#[CoversMethod]` and `#[DataProvider]` attributes required
- Edge cases: historical dates (1800s), boundary conditions, invalid inputs
- Dedicated mutation killer tests for hard-to-kill mutants

## CI/CD

Two GitHub Actions workflows:
- **ci.yml**: Full test suite on PHP 8.3 + 8.4, code quality checks, Codecov upload
- **php.yml**: Sequential build pipeline with auto-release on main
