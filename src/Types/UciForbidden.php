<?php

namespace UciGraphQL\Types;

trait UciForbidden
{
    /**
     * @var array|null
     */
    public static $forbiddenConfigurations = null;

    /**
     * @return bool
     */
    protected function isCorrectForbiddenConfigurations(): bool
    {
        return self::$forbiddenConfigurations !== null &&
                is_array(self::$forbiddenConfigurations);
    }

    /**
     * @param string $configName
     * @return bool
     */
    protected function isCorrectConfigForbiddenConfigurations($configName): bool
    {
        return $this->isCorrectForbiddenConfigurations() &&
                isset(self::$forbiddenConfigurations[$configName]) &&
                is_array(self::$forbiddenConfigurations[$configName]);
    }

    /**
     * @param string $configName
     * @param string $sectionName
     * @return bool
     */
    protected function isCorrectSectionForbiddenConfigurations($configName, $sectionName): bool
    {
        return $this->isCorrectConfigForbiddenConfigurations($configName) &&
                isset(self::$forbiddenConfigurations[$configName][$sectionName]) &&
                is_array(self::$forbiddenConfigurations[$configName][$sectionName]);
    }

    /**
     * Return all configs name forbidden.
     * @return array
     */
    protected function getConfigsForbidden() :array
    {
        if (!$this->isCorrectForbiddenConfigurations()) {
            return [];
        }

        $configsForbidden = [];

        if (is_iterable(self::$forbiddenConfigurations)) {
            foreach (self::$forbiddenConfigurations as $configName => $content) {
                if ($content === true) {
                    array_push($configsForbidden, $configName);
                }
            }
        }

        return $configsForbidden;
    }

    /**
     * Return all sections name forbidden.
     * @param string $configName
     * @return array
     */
    protected function getSectionsForbidden($configName) :array
    {
        if (!$this->isCorrectConfigForbiddenConfigurations($configName)) {
            return [];
        }

        $sectionsForbidden = [];

        if (isset(self::$forbiddenConfigurations[$configName]) && is_iterable(self::$forbiddenConfigurations[$configName])) {
            foreach (self::$forbiddenConfigurations[$configName] as $sectionName => $content) {
                if ($content === true) {
                    array_push($sectionsForbidden, $sectionName);
                }
            }
        }

        return $sectionsForbidden;
    }

    /**
     * Return all options name forbidden.
     * @param string $configName
     * @param string $sectionName
     * @return array
     */
    protected function getOptionsForbidden($configName, $sectionName) :array
    {
        if (!$this->isCorrectSectionForbiddenConfigurations($configName, $sectionName)) {
            return [];
        }

        return isset(self::$forbiddenConfigurations[$configName][$sectionName]) ? self::$forbiddenConfigurations[$configName][$sectionName] : [];
    }
}
