<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Feature;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for accessibility and internationalization scenarios
 */
final class AccessibilityValidationTest extends TestCase
{
    /**
     * Test validation with screen reader formatted input
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testScreenReaderFormattedInput(): void
    {
        // Screen readers might add pauses or formatting
        $screenReaderFormats = [
            '8 0 0 1 0 1 5 0 0 9 0 8 7', // Spaces between each digit
            '800-101-5009-087', // Grouped with hyphens
            '800 101 5009 087', // Grouped with spaces
            '8001015009087', // Normal format
        ];

        foreach ($screenReaderFormats as $screenReaderFormat) {
            $result = SouthAfricanIDValidator::luhnIDValidate($screenReaderFormat);
            self::assertTrue($result, 'Should handle screen reader format: ' . $screenReaderFormat);
        }
    }

    /**
     * Test with various keyboard layouts and input methods
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testInternationalKeyboardLayouts(): void
    {
        // Test IDs that might be entered on different keyboard layouts
        $keyboardVariants = [
            '8001015009087', // Standard
            '８００１０１５００９０８７', // Japanese full-width
            '8OO1O15OO9O87', // Common OCR/typing confusion (O vs 0)
            '800l0l5009087', // Common confusion (l vs 1)
        ];

        // Only the standard format should validate
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($keyboardVariants[0]));
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($keyboardVariants[1]));
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($keyboardVariants[2]));
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($keyboardVariants[3]));
    }

    /**
     * Test with copy-paste artifacts
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCopyPasteArtifacts(): void
    {
        $copyPasteVariants = [
            "\t8001015009087", // Leading tab
            "8001015009087\n", // Trailing newline
            "\r\n8001015009087\r\n", // Windows line endings
            " 8001015009087 ", // Leading/trailing spaces
            "​8001015009087​", // Zero-width spaces
            "‎8001015009087‏", // LTR/RTL marks
        ];

        foreach ($copyPasteVariants as $copyPasteVariant) {
            $result = SouthAfricanIDValidator::luhnIDValidate($copyPasteVariant);
            self::assertTrue($result, "Should handle copy-paste artifacts");
        }
    }

    /**
     * Test with voice input patterns
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testVoiceInputPatterns(): void
    {
        // Voice input might produce these patterns
        $voicePatterns = [
            'eight zero zero one zero one five zero zero nine zero eight seven',
            'eight double zero one zero one five double zero nine zero eight seven',
            'eight hundred and one zero one five thousand and nine zero eight seven',
        ];

        // These should all fail as they're not numeric
        foreach ($voicePatterns as $voicePattern) {
            $result = SouthAfricanIDValidator::luhnIDValidate($voicePattern);
            self::assertFalse($result, 'Should not validate voice pattern: ' . $voicePattern);
        }
    }

    /**
     * Test with Braille display compatibility formats
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testBrailleDisplayCompatibility(): void
    {
        // Braille displays might show numbers in specific formats
        $validId = '8001015009087';

        // Standard numeric format should work
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($validId));

        // Braille Unicode patterns should not validate
        $braillePattern = '⠼⠓⠚⠚⠁⠚⠁⠑⠚⠚⠊⠚⠓⠛'; // Braille numbers
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($braillePattern));
    }

    /**
     * Test with high contrast mode considerations
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testHighContrastModeInput(): void
    {
        // High contrast mode shouldn't affect validation
        // but users might make more input errors

        $highContrastErrors = [
            '8OO1O15OO9O87', // O instead of 0
            'B00101500908T', // B/8 and T/7 confusion
            'S001015009087', // S/5 confusion
        ];

        foreach ($highContrastErrors as $highContrastError) {
            $result = SouthAfricanIDValidator::luhnIDValidate($highContrastError);
            self::assertFalse($result, 'Should not validate high contrast error: ' . $highContrastError);
        }
    }

    /**
     * Test with right-to-left (RTL) language contexts
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testRtlLanguageContext(): void
    {
        // RTL languages might affect string handling
        $rtlFormats = [
            '8001015009087', // LTR
            "\u{202E}8001015009087\u{202C}", // RTL override
            "\u{200F}8001015009087", // RTL mark
        ];

        // Clean numeric string should validate
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($rtlFormats[0]));

        // RTL marks might be sanitised
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($rtlFormats[1]), "RTL override might be sanitised");
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($rtlFormats[2]), "RTL mark might be sanitised");
    }

    /**
     * Test with assistive technology paste buffers
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testAssistiveTechPasteBuffers(): void
    {
        // Assistive tech might add metadata to paste buffers
        $pasteBufferFormats = [
            "ID: 8001015009087",
            "South African ID: 8001015009087",
            "[8001015009087]",
            "(8001015009087)",
            "Copy of 8001015009087",
        ];

        // These contain the valid ID and will be sanitised
        foreach ($pasteBufferFormats as $pasteBufferFormat) {
            $result = SouthAfricanIDValidator::luhnIDValidate($pasteBufferFormat);
            self::assertTrue($result, 'Should extract ID from paste buffer format: ' . $pasteBufferFormat);
        }

        // Test formats without valid ID
        $invalidFormats = [
            "ID: ",
            "South African ID: none",
            "[invalid]",
            "()",
            "Copy of nothing",
        ];

        foreach ($invalidFormats as $invalidFormat) {
            $result = SouthAfricanIDValidator::luhnIDValidate($invalidFormat);
            self::assertFalse($result, 'Should not validate without valid ID: ' . $invalidFormat);
        }
    }

    /**
     * Test with mobile accessibility features
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testMobileAccessibilityFeatures(): void
    {
        // Mobile devices might auto-format numbers
        $mobileFormats = [
            '8001015009087', // Standard
            '800-101-500-9087', // Auto-formatted like phone
            '800.101.500.9087', // Dot separated
            '800 101 500 9 087', // Voice-to-text spacing
        ];

        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($mobileFormats[0]));
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($mobileFormats[1]));
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($mobileFormats[2]));
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($mobileFormats[3]));
    }

    /**
     * Test with cognitive accessibility patterns
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCognitiveAccessibilityPatterns(): void
    {
        // Users with cognitive disabilities might enter IDs in chunks
        $chunkedEntries = [
            '800101-5009087',
            '8001-01-5009087',
            '80-01-01-50-09-087',
            '8001015009087',
        ];

        foreach ($chunkedEntries as $chunkedEntry) {
            $result = SouthAfricanIDValidator::luhnIDValidate($chunkedEntry);
            self::assertTrue($result, 'Should handle chunked entry: ' . $chunkedEntry);
        }
    }

    /**
     * Test with alternative input devices
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testAlternativeInputDevices(): void
    {
        // Alternative input devices might produce different patterns
        $alternativeInputs = [
            '8001015009087', // Standard keyboard
            ' 8001015009087', // Switch device (might add spaces)
            '8001015009087 ', // Eye tracking (might add trailing space)
            '8 001015009087', // Head tracking (might have gaps)
        ];

        foreach ($alternativeInputs as $alternativeInput) {
            $result = SouthAfricanIDValidator::luhnIDValidate($alternativeInput);
            self::assertTrue($result, sprintf("Should handle alternative input: '%s'", $alternativeInput));
        }
    }

    /**
     * Test error recovery for accessibility
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testAccessibleErrorRecovery(): void
    {
        // Common errors that should be clearly invalid
        $accessibilityErrors = [
            '', // Empty
            '123', // Too short
            '80010150090871234567', // Too long
            'ABCDEFGHIJKLM', // Letters
            '800101500908!', // Special character
        ];

        foreach ($accessibilityErrors as $accessibilityError) {
            $result = SouthAfricanIDValidator::luhnIDValidate($accessibilityError);
            self::assertFalse($result, sprintf("Must clearly reject: '%s'", $accessibilityError));
        }
    }
}
