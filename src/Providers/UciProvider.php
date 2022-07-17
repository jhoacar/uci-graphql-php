<?php

declare(strict_types=1);

namespace UciGraphQL\Providers;

/**
 * Represents the Section for the UCI System.
 */
class UciSection
{
    /**
     * @var array
     */
    public $options = [];
}

/**
 * Represents the information for the UCI System.
 */
abstract class UciProvider
{
    /**
     * Return an object with the representation for the UCI System.
     *
     * For example:
     *  {
     *      app:{
     *          port
     *      }
     *  }
     *
     * @return array
     */
    abstract public static function getUciConfiguration(): array;

    /**
     * - If a section is an array is saved as a Array.
     *
     *      - This array is saved with the position described by the uci system
     *
     * - If a section is not an array, so it's saved as a UciSection
     *
     *      - This UciSection has an attribute 'options' for each option in this section
     *
     * @param array|UciSection &$configSection
     * @param string $sectionName
     * @param string $optionName
     * @param array $content
     * @return void
     */
    abstract protected static function getUciSection(&$configSection, $sectionName, $optionName, $content): void;
}
