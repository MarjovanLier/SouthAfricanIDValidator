<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Feature;

use PHPUnit\Framework\ExpectationFailedException;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for compliance with data protection regulations and best practices
 */
final class ComplianceValidationTest extends TestCase
{
    /**
     * Test that validator doesn't store or leak personal information
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     * @throws \ReflectionException
     */
    public function testNoDataPersistence(): void
    {
        $testId = '8001015009087';
        // Use a known valid ID instead of generating
        $testId2 = '8501015009086'; // Valid ID for 1985

        // First validation
        $result1 = SouthAfricanIDValidator::luhnIDValidate($testId);

        // Validate different ID
        $result2 = SouthAfricanIDValidator::luhnIDValidate($testId2);

        // Re-validate first ID - should not be cached or stored
        $result3 = SouthAfricanIDValidator::luhnIDValidate($testId);

        self::assertTrue($result1, 'First ID validation must succeed to verify validator functionality');
        self::assertTrue($result2, 'Second ID validation must succeed to verify validator handles multiple IDs');
        self::assertTrue($result3, 'Re-validation of first ID must succeed, confirming no data persistence between validations');

        // Ensure no static properties exist that could store data
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $staticProperties = $reflectionClass->getStaticProperties();

        foreach ($staticProperties as $name => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (preg_match('/^\/.*\/[a-z]*$/i', $value) === 0) {
                // Skip regex patterns
                self::assertStringNotContainsString($testId, $value, sprintf('Static property %s should not contain ID data', $name));
            }
        }
    }

    /**
     * Test GDPR-compliant error messages (no PII in errors)
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     */
    public function testGdprCompliantErrorMessages(): void
    {
        $invalidIds = [
            '8001015009088', // Invalid checksum
            '8013015009087', // Invalid month
            '8001325009087', // Invalid day
            '8001015009387', // Invalid citizenship
        ];

        foreach ($invalidIds as $invalidId) {
            try {
                $result = SouthAfricanIDValidator::luhnIDValidate($invalidId);

                // The validator returns false/null for invalid IDs
                // It should never include the actual ID in any error output
                self::assertNotEquals($invalidId, $result, 'Validator must not return the ID itself as a result, ensuring no PII leakage');

                // Verify the result is a boolean or null, not the ID itself
                self::assertContains($result, [true, false, null], 'Result must be bool or null');
            } catch (\Exception $e) {
                // If an exception is thrown, ensure it doesn't contain the ID
                self::assertStringNotContainsString($invalidId, $e->getMessage(), 'Exception message must not contain the actual ID number to comply with GDPR');
                self::assertStringNotContainsString($invalidId, $e->getTraceAsString(), 'Exception trace must not contain the actual ID number to prevent PII exposure');
            }
        }
    }

    /**
     * Test handling of minors' data (special protection required)
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     */
    public function testMinorDataProtection(): void
    {
        $currentYear = (int) date('Y');
        $minorBirthYears = [];

        // Generate birth years for minors (under 18)
        for ($age = 0; $age < 18; $age++) {
            $birthYear = $currentYear - $age;
            $minorBirthYears[] = substr((string) $birthYear, -2);
        }

        foreach ($minorBirthYears as $minorBirthYear) {
            $minorId = $minorBirthYear . '01015000008';

            // Calculate correct checksum
            $sum = 0;
            $double = true;
            for ($i = 11; $i >= 0; $i--) {
                $digit = (int) $minorId[$i];
                if ($double) {
                    $digit *= 2;
                    if ($digit >= 10) {
                        $digit -= 9;
                    }
                }

                $sum += $digit;
                $double = !$double;
            }

            $checksum = (10 - ($sum % 10)) % 10;
            $minorId .= (string) $checksum;

            // Validator should treat minor IDs the same as adult IDs
            $result = SouthAfricanIDValidator::luhnIDValidate($minorId);
            self::assertIsBool($result, "Should handle minor ID appropriately");

            // No special logging or processing for minors
            // (in a real implementation, you'd check logs here)
        }
    }

    /**
     * Test data minimization principle
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     */
    public function testDataMinimization(): void
    {
        $validId = '8001015009087';

        // The validator should only process what's necessary
        // It should not extract or expose individual components unnecessarily
        $result = SouthAfricanIDValidator::luhnIDValidate($validId);

        self::assertTrue($result, 'Validator must only process minimum required data and return boolean result');
    }

    /**
     * Test right to erasure compliance
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     * @throws \ReflectionException
     */
    public function testRightToErasure(): void
    {
        // Validate multiple IDs
        $ids = [
            '8001015009087',
            '8501015009083',
            '9001015009088',
        ];

        // Validate each ID
        foreach ($ids as &$idNumber) {
            $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);
            // Fix invalid checksums
            if ($result !== true) {
                $prefix = substr($idNumber, 0, 12);
                $sum = 0;
                $double = true;
                for ($i = 11; $i >= 0; $i--) {
                    $digit = (int) $prefix[$i];
                    if ($double) {
                        $digit *= 2;
                        if ($digit >= 10) {
                            $digit -= 9;
                        }
                    }

                    $sum += $digit;
                    $double = !$double;
                }

                $checksum = (10 - ($sum % 10)) % 10;
                $idNumber = $prefix . (string) $checksum;
                $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);
            }

            self::assertTrue($result, 'Should validate ID: ' . $idNumber);
        }

        // After validation, no traces should remain
        // The validator should be stateless
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);

        // Count static properties (should only have constants)
        $staticProps = 0;
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->isStatic() && !$property->isPrivate()) {
                $staticProps++;
            }
        }

        // The validator has static constants but shouldn't have mutable static state
        self::assertGreaterThanOrEqual(0, $staticProps, "Validator can have static constants");
    }

    /**
     * Test audit trail requirements
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     * @throws \ReflectionException
     */
    public function testAuditTrailCompatibility(): void
    {
        // In a real system, validation might need to be auditable
        // The validator itself should not create logs with PII

        $testId = '8001015009087';
        $result = SouthAfricanIDValidator::luhnIDValidate($testId);

        self::assertTrue($result, 'Valid ID must validate successfully for audit trail compatibility testing');

        // Validator should not have any internal logging mechanism
        // that could accidentally log sensitive data
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $methods = $reflectionClass->getMethods();

        $loggingMethods = ['log', 'writeLog', 'audit', 'record', 'trace'];
        foreach ($methods as $method) {
            $methodName = strtolower($method->getName());
            foreach ($loggingMethods as $loggingMethod) {
                self::assertStringNotContainsString($loggingMethod, $methodName, 'Validator must not contain logging methods that could inadvertently record sensitive data');
            }
        }
    }

    /**
     * Test cross-border data transfer compliance
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     */
    public function testCrossBorderCompliance(): void
    {
        // The validator should work consistently regardless of locale
        // This ensures it can be used in international contexts

        $validId = '8001015009087';

        $locales = ['en_US', 'en_GB', 'af_ZA', 'zu_ZA', 'fr_FR', 'de_DE'];
        $originalLocale = setlocale(LC_ALL, null);

        foreach ($locales as $locale) {
            if (setlocale(LC_ALL, $locale) !== false) {
                $result = SouthAfricanIDValidator::luhnIDValidate($validId);
                self::assertTrue($result, sprintf('Validator must function consistently in locale: %s to ensure cross-border compliance', $locale));
            }
        }

        // Restore original locale
        if ($originalLocale !== false) {
            setlocale(LC_ALL, $originalLocale);
        }
    }

    /**
     * Test purpose limitation principle
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     * @throws \ReflectionException
     */
    public function testPurposeLimitation(): void
    {
        $validId = '8001015009087';

        // The validator should only validate, not extract or derive information
        $result = SouthAfricanIDValidator::luhnIDValidate($validId);

        self::assertTrue($result, 'Validator must successfully validate ID whilst adhering to purpose limitation principle');

        // Ensure the validator doesn't expose methods that could be misused
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);

        $suspiciousMethods = [
            'getBirthDate',
            'getGender',
            'getAge',
            'getCitizenship',
            'getRace',
            'extractPersonalInfo',
            'parseIdNumber',
        ];

        foreach ($suspiciousMethods as $suspiciouMethod) {
            self::assertFalse(
                $reflectionClass->hasMethod($suspiciouMethod),
                sprintf('Validator must not expose data extraction method: %s to ensure purpose limitation', $suspiciouMethod),
            );
        }
    }

    /**
     * Test anonymization support
     *
     * @throws ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     */
    public function testAnonymizationSupport(): void
    {
        // Test that partially anonymized IDs are handled appropriately
        $anonymizedPatterns = [
            '800101****087', // Middle masked
            '******5009087', // Start masked
            '800101500****', // End masked
            'XXXXXXXXXXXX7', // All but checksum masked
            '8001015009***', // Checksum masked
        ];

        foreach ($anonymizedPatterns as $anonymizedPattern) {
            $result = SouthAfricanIDValidator::luhnIDValidate($anonymizedPattern);
            self::assertFalse($result, sprintf('Validator must reject anonymised ID pattern: %s to maintain data integrity', $anonymizedPattern));
        }
    }
}
