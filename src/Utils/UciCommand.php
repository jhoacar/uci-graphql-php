<?php

declare(strict_types=1);

namespace UciGraphQL\Utils;

use stdClass;

/**
 * Represents the Section for the UCI System.
 */
class UciSection extends stdClass
{
    /**
     * @var array
     */
    public $options;
}

/**
 * Represents all translation from UCI Command to Array Object in PHP.
 */
class UciCommand extends Command
{
    /**
     * This is the text that shows the uci system when a resource is not found.
     * @var string
     */
    public const NOT_FOUND = 'not found';

    /**
     * This is the command to extract all the information from the UCI System.
     * @var string
     */
    public const UCI_SHOW = 'uci show';

    /**
     * Return a string to use in a command shell.
     * @param string $input
     * @return string
     */
    private static function cleanInput(string $input): string
    {
        return escapeshellcmd(escapeshellarg($input));
    }

    /**
     * Return the output for the specified resource
     * Validate the each field used as input
     * Also if the resource is not found return an empty string.
     * @param string $config file to find in /etc/config for default
     * @param string $section to find in the config
     * @param string $option to find in the section for the config
     * @return string
     */
    public static function get(string $config, string $section, string $option): string
    {
        $config = self::cleanInput($config);
        $section = self::cleanInput($section);
        $option = self::cleanInput($option);
        $result = parent::execute("uci get $config.$section.$option");

        return str_contains($result, self::NOT_FOUND) ? '' : $result;
    }

    /**
     * Return an index in the string contained between [] or -1 otherwise
     * For example:
     *         For the input => '@system[14]'
     *         You obtain => 14.
     *
     *         For the input => 'system20'
     *         You obtain => -1
     * @param string $section
     * @return int
     */
    private static function getIndexSection(string $section): int
    {
        $matches = [];
        $isFound = preg_match('(\[([0-9]*)\])', $section, $matches);
        if ($isFound) {
            return intval($matches[1]);
        }

        return -1;
    }

    /**
     * Return the name section that is contained between @ and [ or same string otherwise
     * For example:
     *          For the input => '@system[14]'
     *          You obtain => 'system'.
     *
     *          For the input => 'system20'
     *          You obtain => 'system20'
     * @param string $section
     * @return string
     */
    private static function getNameSection(string $section): string
    {
        $matches = [];
        $isFound = preg_match('(@([\s\S]*)\[)', $section, $matches);
        if ($isFound) {
            return $matches[1];
        }

        return $section;
    }

    /**
     * Execute a command to extract all the information from the UCI System.
     * @return array
     */
    public static function getConfigurationCommand(): array
    {
        return explode(PHP_EOL, parent::execute(self::UCI_SHOW));
    }

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
    public static function getUciConfiguration(): array
    {
        $CONFIGURATION = 0;
        $SECTION = 1;
        $OPTIONS = 2;

        $uciConfig = [];
        // When we use 'static::' we guarantee override methods, instead of 'self::'
        $configurations = static::getConfigurationCommand();

        foreach ($configurations as $info) {
            if (!strlen($info)) {
                continue;
            }

            $division = explode('=', $info);

            if (count($division) < 2) {
                continue;
            }

            [$info, $content] = $division;

            $removedBlankSpaces = preg_replace('/\s/', '', $info);
            $information = explode('.', $removedBlankSpaces ? $removedBlankSpaces : '');

            if (count($information) < 3) {
                continue;
            }

            $config = $information[$CONFIGURATION];
            $section = $information[$SECTION];
            $option = explode('=', $information[$OPTIONS])[0];
            $content = explode(' ', $content);

            if (empty($uciConfig[$config])) {
                $uciConfig[$config] = [];
            }

            $sectionName = self::getNameSection($section);

            if (empty($uciConfig[$config][$sectionName])) {
                $uciConfig[$config][$sectionName] = [];
            }

            self::getUciSection($uciConfig[$config][$sectionName], $section, $option, $content);
        }

        return $uciConfig;
    }

    /**
     * - If a section is an array is saved as a Array.
     *
     *      - This array is saved with the position described by the uci system
     *
     * - If a section is not an array, so it's saved as a stdClass
     *
     *      - This stdClass has an attribute 'options' for each option in this section
     *
     * @param array|UciSection &$configSection
     * @param string $sectionName
     * @param string $optionName
     * @param array $content
     * @return void
     */
    private static function getUciSection(array|UciSection &$configSection, string $sectionName, string $optionName, array $content): void
    {
        $isArraySection = str_contains($sectionName, '@');
        $indexArraySection = $isArraySection ? self::getIndexSection($sectionName) : -1;

        if ($isArraySection) {
            if (empty($configSection)) {
                $configSection = [];
            }

            if (is_array($configSection)) {
                if (empty($configSection[$indexArraySection])) {
                    $configSection[$indexArraySection] = [];
                }
                $configSection[$indexArraySection][$optionName] = $content;
            }
        } else {
            if (empty($configSection)) {
                $configSection = new UciSection();
            }
            if ($configSection instanceof UciSection) {
                if (empty($configSection->options)) {
                    $configSection->options = [];
                }
                $configSection->options[$optionName] = $content;
            }
        }
    }
}
