<?php

declare(strict_types=1);

namespace UciGraphQL\Types;

trait UciDescription
{
    /**
     * Return a description for the option.
     * @param string $optionName
     * @param string $sectionName
     * @param string $configName
     * @return string
     */
    public function getOptionDescription($optionName, $sectionName, $configName): string
    {
        return "Option $optionName for $sectionName in $configName configuration";
    }

    /**
     * Return a description for the section.
     * @param string $sectionName
     * @param string $configName
     * @return string
     */
    public function getsectionDescription($sectionName, $configName): string
    {
        return "Section $sectionName in $configName configuration";
    }

    /**
     * Return a description for the config.
     * @param string $configName
     * @return string
     */
    public function getConfigDescription($configName): string
    {
        return "$configName configuration in the UCI System";
    }
}
