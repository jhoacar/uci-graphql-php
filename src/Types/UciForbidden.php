<?php

declare(strict_types=1);

namespace UciGraphQL\Types;

trait UciForbidden
{
    /**
     * @var array|null
     */
    protected $forbiddenConfigurations = null;

    /**
     * @return bool
     */
    protected function isCorrectForbiddenConfigurations(): bool
    {
        return $this->forbiddenConfigurations !== null &&
                is_array($this->forbiddenConfigurations);
    }

    /**
     * @param string $configName
     * @return bool
     */
    protected function isCorrectConfigForbiddenConfigurations($configName): bool
    {
        return $this->isCorrectForbiddenConfigurations() &&
                isset($this->forbiddenConfigurations[$configName]) &&
                is_array($this->forbiddenConfigurations[$configName]);
    }

    /**
     * @param string $configName
     * @param string $sectionName
     * @return bool
     */
    protected function isCorrectSectionForbiddenConfigurations($configName, $sectionName): bool
    {
        return $this->isCorrectConfigForbiddenConfigurations($configName) &&
                isset($this->forbiddenConfigurations[$configName][$sectionName]) &&
                is_array($this->forbiddenConfigurations[$configName][$sectionName]);
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

        if (is_iterable($this->forbiddenConfigurations)) {
            foreach ($this->forbiddenConfigurations as $configName => $content) {
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

        if (isset($this->forbiddenConfigurations[$configName]) && is_iterable($this->forbiddenConfigurations[$configName])) {
            foreach ($this->forbiddenConfigurations[$configName] as $sectionName => $content) {
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

        return isset($this->forbiddenConfigurations[$configName][$sectionName]) ? $this->forbiddenConfigurations[$configName][$sectionName] : [];
    }
}
