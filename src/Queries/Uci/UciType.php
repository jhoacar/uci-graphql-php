<?php

declare(strict_types=1);

namespace UciGraphQL\Queries\Uci;

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
    /**
     * @var array
     */
    public $uciInfo = [];

    /**
     * @var UciCommand|null
     */
    public static $commandExecutor = null;

    /**
     * @var array
     */
    public static $forbiddenConfigurations = [];

    /**
     * Construct all the type with dinamyc schema from the UCI System.
     */
    public function __construct()
    {
        if (self::$commandExecutor === null) {
            self::$commandExecutor = new UciCommand();
        }

        $config = [
            'name' => 'uci',
            'description' => 'Router Configuration',
            'fields' => $this->getUciFields(),
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                return $this->uciInfo[$info->fieldName];
            },
        ];
        parent::__construct($config);
    }

    /**
     * Return an array with unique keys for each array.
     * @param array $section
     * @return array
     */
    private function getUniqueKeys($section): array
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
     * Return all fields in the uci configuration using GraphQL sintax.
     * @return array
     */
    private function getUciFields(): array
    {
        if (self::$commandExecutor === null) {
            return [];
        }

        $this->uciInfo = self::$commandExecutor->getUciConfiguration();

        $uciFields = [];
        foreach ($this->uciInfo as $configName => $sections) {
            $configFields = [];

            foreach ($sections as $sectionName => $section) {
                $sectionFields = [];
                $isArraySection = is_array($section);

                $allOptions = $isArraySection ? $this->getUniqueKeys($section) : array_keys($section->options);

                foreach ($allOptions as $optionName) {
                    $sectionFields[$optionName] = $this->getOptionType($configName, $sectionName, $optionName);
                }

                $configFields[$sectionName] = $this->getSectionType($configName, $sectionName, $sectionFields, $isArraySection);
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
    private function getConfigurationType($configName, $configFields): array
    {
        return [
            'description' => "$configName UCI Configuration",
            'type' => new ObjectType([
                'name' => $configName,
                'fields' => $configFields,
                'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                    return $value[$info->fieldName];
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
    private function getSectionType($configName,  $sectionName,  $sectionFields,  $isArray): array
    {
        $configObject = [
            'name' => $configName . '_' . $sectionName,
            'fields' => $sectionFields,
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                if ($value instanceof UciSection) {
                    return $value->options[$info->fieldName];
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
                return $value[$info->fieldName];
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
    private function getOptionType($configName,  $sectionName,  $optionName): array
    {
        return [
            'name' => $optionName,
            'description' => "Option $optionName for $sectionName in $configName configuration",
            'type' => Type::listOf(Type::string()),
        ];
    }
}
