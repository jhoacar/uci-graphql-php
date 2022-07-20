<?php

declare(strict_types=1);

namespace UciGraphQL\Providers;

use UciGraphQL\Mutations\Uci\UciMutation;
use UciGraphQL\Queries\Uci\UciQuery;
use UciGraphQL\Utils\Command;

/**
 * Represents all translation from UCI Command to Array Object in PHP.
 */
class UciCommandProvider extends UciProvider
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
    private static function cleanInput($input): string
    {
        return escapeshellcmd(escapeshellarg($input));
    }

    /**
     * Return a string to use in a value for the uci command.
     * @param string $value
     * @return string
     */
    private function cleanUciValue($value)
    {
        return escapeshellarg($value);
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
    public static function get($config, $section, $option): string
    {
        $config = self::cleanInput($config);
        $section = self::cleanInput($section);
        $option = self::cleanInput($option);
        $result = Command::execute("uci get $config.$section.$option");

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
    private static function getIndexSection($section): int
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
    private static function getNameSection($section): string
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
        $result = Command::execute(self::UCI_SHOW);

        return str_contains($result, self::NOT_FOUND) ? [] : explode(PHP_EOL, $result);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    protected static function getUciSection(&$configSection, $sectionName, $optionName, $content): void
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

    /**
     * @inheritdoc
     */
    public function dispatchAction($action, $config, $section, $indexSection, $option, $value): array
    {
        $configCleaned = self::cleanInput($config);
        $sectionCleaned = self::cleanInput($section);
        $optionCleaned = self::cleanInput($option);
        $valueCleaned = self::cleanUciValue($value);
        // Extract the name in the enum class and convert to lower case for the uci command
        $verb = strtolower($action);

        $commandToExecute = '';
        if ($indexSection === parent::IS_OBJECT_SECTION) {
            $commandToExecute = "uci $verb $configCleaned.$sectionCleaned.$optionCleaned=$valueCleaned;";
        } elseif ($indexSection === parent::ALL_INDEXES_SECTION) {
            $allIndexes = count(UciMutation::uci()->uciInfo[$configCleaned][$sectionCleaned]);
            foreach (range(0, $allIndexes - 1) as $index) {
                $commandToExecute .= "uci $verb $configCleaned.@$sectionCleaned[$index].$optionCleaned=$valueCleaned;";
            }
        } else {
            $commandToExecute = "uci $verb $configCleaned.@$sectionCleaned[$indexSection].$optionCleaned=$valueCleaned;";
        }

        $resultCode = 0;

        $resultCommand = Command::execute($commandToExecute, $resultCode);

        if ($resultCode !== Command::NO_ERRORS) {
            return [$resultCommand];
        }

        $resultCommand = Command::execute("uci commit $configCleaned", $resultCode);

        if ($resultCode !== Command::NO_ERRORS) {
            return [$resultCommand];
        }

        array_push($this->services, $configCleaned);

        UciQuery::uci()->setUciInfo(self::getUciConfiguration());
        UciMutation::uci()->setUciInfo(self::getUciConfiguration());

        $result = [];

        $section = UciQuery::uci()->uciInfo[$config][$section];
        if ($indexSection === parent::IS_OBJECT_SECTION && $section instanceof UciSection) {
            $result = $section->options[$option];
        } elseif (is_array($section) && $indexSection >= 0) {
            $result = $section[$indexSection][$option];
        }

        return $result;
    }
}
