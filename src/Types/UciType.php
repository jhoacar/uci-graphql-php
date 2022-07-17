<?php

declare(strict_types=1);

namespace UciGraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use UciGraphQL\Utils\UciCommand;
use UciGraphQL\Utils\UciSection;

/**
 * Class used for load all schema for the UCI System in GraphQL.
 */
class UciType extends ObjectType
{
    use UciForbidden;
    /**
     * @var array
     */
    public $uciInfo = [];

    /**
     * @var UciCommand|null
     */
    public static $commandExecutor = null;

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
        if (self::$commandExecutor === null || !method_exists(self::$commandExecutor, 'getUciConfiguration')) {
            return [];
        }

        $this->uciInfo = self::$commandExecutor->getUciConfiguration();

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
            'description' => "$configName UCI Configuration",
            'type' => new ObjectType([
                'name' => 'query_' . $configName,
                'fields' => $configFields,
                'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                    return $value[$info->fieldName] ?? null;
                },
            ]),
        ];
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
        $configObject = [
            'name' => 'query_' . $configName . '_' . $sectionName,
            'fields' => $sectionFields,
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                if ($value instanceof UciSection) {
                    return $value->options[$info->fieldName] ?? null;
                } else {
                    return $value[$info->fieldName] ?? null;
                }
            },
        ];

        $configArray = [
            'name' => $sectionName,
            'description' => "List of $sectionName section for $configName",
            'type' => Type::listOf(new ObjectType($configObject)),
            'resolve' => function ($value, $args, $context, ResolveInfo $info) {
                return $value[$info->fieldName] ?? null;
            },
        ];

        return $isArray ? $configArray : [
            'description' => "Section $sectionName for $configName",
            'type' => new ObjectType($configObject),
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
            'description' => "Option $optionName for $sectionName in $configName configuration",
            'type' => Type::listOf(Type::string()),
        ];
    }
}
