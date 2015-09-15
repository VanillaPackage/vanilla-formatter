<?php

namespace Rentalhost\VanillaFormatter;

/**
 * Class Formatter
 * @package Rentalhost\VanillaFormatter
 */
class Formatter
{
    /**
     * Store formats from res/formats.json.
     * @var array
     */
    private static $formats;

    /**
     * Normalize a value.
     *
     * @param  string $value Value to normalize.
     * @param  string $type  Normalization type.
     *
     * @return string
     */
    public static function normalize($value, $type = null)
    {
        $type = strtolower($type);

        // Default expression will remove all not-numbers.
        $replaceExpression = '\d';

        // Phone types will not exclude "+" character.
        if ($type === 'phone') {
            $replaceExpression = "\\d+";
        }

        // Normalize string.
        return preg_replace("/[^{$replaceExpression}]/", null, $value);
    }

    /**
     * Format a value.
     *
     * @param  string $value  Value to format.
     * @param  string $type   Formatter type.
     * @param  string $region Formatter region.
     *
     * @return string
     */
    public static function format($value, $type = null, $region = null)
    {
        $valueNormalized = self::normalize($value, $type);
        $matchedFormat = self::getCompatibleFormat($valueNormalized, strtolower($type), strtolower($region) ?: null);

        // If not format was matched, returns the original value.
        if (!$matchedFormat) {
            return $value;
        }

        $matchedFormatNormalized = preg_replace('/\d/', '#', $matchedFormat['format']);
        $valueNormalizedLength = strlen($valueNormalized);

        // Replace all "#" characters with normalized value.
        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $valueNormalizedLength; $i++) {
            $matchedFormatNormalized = preg_replace('/#/', $valueNormalized[$i], $matchedFormatNormalized, 1);
        }

        return $matchedFormatNormalized;
    }

    /**
     * Load and return formats.
     * @return array
     */
    private static function getFormats()
    {
        // Return in-cache formats.
        if (self::$formats) {
            return self::$formats;
        }

        // Load formats and return.
        return self::$formats = json_decode(file_get_contents(__DIR__ . '/../../res/formats.json'), true);
    }

    /**
     * Match formats by type and region.
     *
     * @param  string $value  Normalized value to format.
     * @param  string $type   Format type.
     * @param  string $region Format region.
     *
     * @return array|null Matched format.
     */
    private static function getCompatibleFormat($value, $type, $region)
    {
        foreach (self::getFormats() as $format) {
            if ($format['type'] !== $type) {
                // Filter by type.
                continue;
            }

            if ($format['length'] !== strlen($value)) {
                // Filter by length.
                continue;
            }

            // Filter by region.
            if ($region === null) {
                // 1. If region was not defined, so ignore all formats with region.
                if (array_key_exists('region', $format)) {
                    continue;
                }
            }
            else {
                // 2. If region was defined, so ignore all format without region.
                // 3. If region was defined, so ignore formats with differents regions.
                if (!array_key_exists('region', $format) ||
                    $format['region'] !== $region
                ) {
                    continue;
                }
            }

            // Normalize format to regular expression.
            $formatExpression = preg_replace([ '/[^\d#]/', '/#/' ], [ null, '\d' ], $format['format']);

            // If match format expression, so return matched format.
            if (preg_match("/^{$formatExpression}$/", $value)) {
                return $format;
            }
        }

        return null;
    }
}
