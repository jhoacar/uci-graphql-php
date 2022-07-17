<?php

declare(strict_types=1);

namespace UciGraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use UciGraphQL\Providers\UciProvider;
use UciGraphQL\Providers\UciSection;

/**
 * Class used for load all schema for the UCI System in GraphQL.
 */
abstract class UciType extends ObjectType
{
    use UciForbidden;
    use UciDescription;
    /**
     * @var array
     */
    public array $uciInfo = [];

    /**
     * @var UciProvider|null
     */
    public static UciProvider|null $provider = null;

    /**
     * @var array
     */
    private array $uciConfigTypes = [];

    /**
     * @var array
     */
    private array $uciSectionTypes = [];

    /**
     * Construct all the type with dinamyc schema from the UCI System.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * Return an array with unique keys for each array.
     * @param array $section
     * @return array
     */
    protected function getUniqueKeys($section): array
    {
        /* Load All Unique Keys for the array */
        $allOptions = [];
        foreach ($section as $options) {
            foreach ($options as $optionName => $content) {
                $allOptions[$optionName] = true;
            }
        }

        return array_keys($allOptions);
    }

    /**
     * Return all options available using other sections in the configuration file.
     * @param array $sections All sections available
     * @param array|UciSection $section Section to evaluate
     * @return array
     */
    protected function getAllOptions($sections, $section): array
    {
        if (is_array($section)) {
            return $this->getUniqueKeys($section);
        }

        if (!($section instanceof UciSection)) {
            return [];
        }

        $options = [];
        foreach ($sections as $compatibleSection) {
            if ($compatibleSection instanceof UciSection) {
                foreach ($compatibleSection->options as $optionName => $content) {
                    $options[$optionName] = true;
                }
            }
        }

        return array_keys($options);
    }

    /**
     * Return all fields in the uci configuration using GraphQL sintax.
     * @return array
     */
    protected function getUciFields(): array
    {
        if (self::$provider === null) {
            return [];
        }

        $this->uciInfo = self::$provider->getUciConfiguration();

        $uciFields = [];
        $configsForbidden = $this->getConfigsForbidden();

        foreach ($this->uciInfo as $configName => $sections) {
            if (in_array($configName, $configsForbidden)) {
                continue;
            }
            $configFields = [];

            $sectionsForbidden = $this->getSectionsForbidden($configName);

            foreach ($sections as $sectionName => $section) {
                if (in_array($sectionName, $sectionsForbidden)) {
                    continue;
                }
                $sectionFields = [];
                $allOptions = $this->getAllOptions($sections, $section);
                $optionsForbidden = $this->getOptionsForbidden($configName, $sectionName);

                foreach ($allOptions as $optionName) {
                    if (in_array($optionName, $optionsForbidden)) {
                        continue;
                    }
                    $sectionFields[$optionName] = $this->getOptionType($configName, $sectionName, $optionName);
                }

                $configFields[$sectionName] = $this->getSectionType($configName, $sectionName, $sectionFields, is_array($section));
            }
            $uciFields[$configName] = $this->getConfigurationType($configName, $configFields);
        }

        return $uciFields;
    }

    /**
     * Return the schema for the configuration of UCI.
     * @param string $configName
     * @param array $configFields
     * @return array
     */
    protected function getConfigurationType($configName, $configFields): array
    {
        return [
            'description' => $this->getConfigDescription($configName),
            'type' => $this->getUniqueConfigType($configName, $configFields),
        ];
    }

    /**
     * Return an unique section type without repeat.
     * @param string $configName
     * @param string $sectionName
     * @param array $sectionFields
     * @return ObjectType
     */
    private function getUniqueSectionType($configName, $sectionName, $sectionFields): ObjectType
    {
        if (!empty($this->uciSectionTypes[$sectionName])) {
            return $this->uciSectionTypes[$sectionName];
        }

        $configObject = [
            'name' => $this->getSectionName($configName, $sectionName),
            'fields' => $sectionFields,
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                if ($value instanceof UciSection) {
                    return $value->options[$info->fieldName] ?? null;
                } else {
                    return $value[$info->fieldName] ?? null;
                }
            },
        ];

        return $this->uciSectionTypes[$sectionName] = new ObjectType($configObject);
    }

    /**
     * Return an unique config type without repeat.
     * @param string $configName
     * @param array $configFields
     * @return ObjectType
     */
    private function getUniqueConfigType($configName, $configFields): ObjectType
    {
        if (!empty($this->uciConfigTypes[$configName])) {
            return $this->uciConfigTypes[$configName];
        }

        return $this->uciConfigTypes[$configName] = new ObjectType([
            'name' => $this->getConfigName($configName),
            'fields' => $configFields,
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                return $value[$info->fieldName] ?? null;
            },
        ]);
    }

    /**
     * Return the schema for the section in the configuration of UCI.
     * @param string $configName
     * @param string $sectionName
     * @param array $sectionFields
     * @param bool $isArray
     * @return array
     */
    protected function getSectionType($configName, $sectionName, $sectionFields, $isArray): array
    {
        $configArray = [
            'name' => $sectionName,
            'description' => $this->getsectionDescription($sectionName, $configName),
            'type' => Type::listOf($this->getUniqueSectionType($configName, $sectionName, $sectionFields)),
            'resolve' => function ($value, $args, $context, ResolveInfo $info) {
                return $value[$info->fieldName] ?? null;
            },
        ];

        return $isArray ? $configArray : [
            'description' => $this->getsectionDescription($sectionName, $configName),
            'type' => $this->getUniqueSectionType($configName, $sectionName, $sectionFields),
        ];
    }

    /**
     * Return the schema for the options by section in the configuration of UCI.
     * @param string $configName
     * @param string $sectionName
     * @param string $optionName
     * @return array
     */
    protected function getOptionType($configName, $sectionName, $optionName): array
    {
        return [
            'name' => $optionName,
            'description' => $this->getOptionDescription($optionName, $sectionName, $configName),
            'type' => Type::listOf(Type::string()),
        ];
    }

    /**
     * Return correct name for the option ObjectType.
     * @param string $configName
     * @param string $sectionName
     * @param string $optionName
     * @return string
     */
    abstract public function getOptionName($configName, $sectionName, $optionName): string;

    /**
     * Return correct name for the section ObjectType.
     * @param string $configName
     * @param string $sectionName
     * @return string
     */
    abstract public function getSectionName($configName, $sectionName): string;

    /**
     * Return correct name for the config ObjectType.
     * @param string $configName
     * @return string
     */
    abstract public function getConfigName($configName): string;
}
