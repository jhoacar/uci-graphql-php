<?php

declare(strict_types=1);

namespace UciGraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use UciGraphQL\Context;
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
    public $uciInfo = [];

    /**
     * @var UciProvider|null
     */
    protected $provider = null;

    /**
     * @var array
     */
    protected $uciConfigTypes = [];

    /**
     * @var array
     */
    protected $uciSectionTypes = [];

    /**
     * @var array
     */
    protected $uciFields = [];

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
     * @param array $uciInfo
     */
    public function setUciInfo($uciInfo): void
    {
        $this->uciInfo = $uciInfo;
    }

    /**
     * Return all fields in the uci configuration using GraphQL sintax.
     * @return array
     */
    protected function getUciFields(): array
    {
        if ($this->provider === null) {
            return [];
        }

        $this->uciInfo = $this->provider->getUciConfiguration();

        $this->uciFields = [];
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
            $this->uciFields[$configName] = $this->getConfigurationType($configName, $configFields);
        }

        return $this->uciFields;
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
    protected function getUniqueSectionType($configName, $sectionName, $sectionFields): ObjectType
    {
        if (!empty($this->uciSectionTypes[$sectionName])) {
            return $this->uciSectionTypes[$sectionName];
        }

        $configObject = [
            'name' => $this->getSectionName($configName, $sectionName),
            'description' => $this->getsectionDescription($sectionName, $configName),
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
    protected function getUniqueConfigType($configName, $configFields): ObjectType
    {
        if (!empty($this->uciConfigTypes[$configName])) {
            return $this->uciConfigTypes[$configName];
        }

        return $this->uciConfigTypes[$configName] = new ObjectType([
            'name' => $this->getConfigName($configName),
            'description' => $this->getConfigDescription($configName),
            'fields' => $configFields,
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                return $value[$info->fieldName] ?? null;
            },
        ]);
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function isRegexString($string): bool
    {
        return preg_match("/^\/.+\/[a-z]*$/i", $string) === 1;
    }

    /**
     * @param string $string
     * @return string
     */
    protected function cleanRegexString($string): string
    {
        return strtolower(str_replace('\'', '', $string));
    }

    /**
     * Resolve the array using filters.
     * @param array $value
     * @param array $args
     * @param Context|null $context
     * @param ResolveInfo $info
     * @return array|null
     */
    protected function filterResolverArraySection($value, $args, $context, ResolveInfo $info)
    {
        if ($context !== null) {
            $context->isArraySection = true;
        }
        if (empty($args)) {
            if ($context !== null) {
                $context->indexSection = UciProvider::ALL_INDEXES_SECTION;
            }

            return $value[$info->fieldName] ?? null;
        } elseif (isset($args['index']) && !empty($value[$info->fieldName])) {
            if ($context !== null) {
                $context->indexSection = (int) $args['index'];
            }
            if ((int) $args['index'] >= count($value[$info->fieldName])) {
                return null;
            }

            return array_slice((array) $value[$info->fieldName], (int) $args['index'], (int) $args['index'] + 1);
        } elseif (!empty($value[$info->fieldName]) && is_array($value[$info->fieldName])) {
            return array_filter($value[$info->fieldName], function ($section) use ($args) {
                $match = false;
                foreach ($args as $arg => $value) {
                    if (isset($section[$arg])) {
                        foreach ($section[$arg] as $sectionToSearch) {
                            if ($this->isRegexString($value)) {
                                if (preg_match($value, $sectionToSearch)) {
                                    $match = true;
                                }
                            } else {
                                $value = $this->cleanRegexString($value);
                                $sectionToSearch = $this->cleanRegexString($sectionToSearch);

                                if (str_contains($sectionToSearch, $value)) {
                                    $match = true;
                                }
                            }
                        }
                    }
                }

                return $match;
            });
        }

        return null;
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
        $arguments = array_reduce(array_keys($sectionFields), function ($prev, $argument) use ($configName, $sectionName) {
            if ($prev === null) {
                $prev = [];
            }
            $prev[$argument] = [
                'type' => Type::string(),
                    'description' => "$argument to search in the $sectionName section in $configName configuration of the UCI System",
            ];

            return $prev;
        });

        $configArray = [
            'name' => $sectionName,
            'description' => $this->getsectionDescription($sectionName, $configName),
            'args' => array_merge($arguments, [
                'index' => [
                    'type' => Type::int(),
                    'description' => "Index of the array in the $sectionName section in $configName configuration of the UCI System",
                ],
            ]),
            'type' => Type::listOf($this->getUniqueSectionType($configName, $sectionName, $sectionFields)),
            'resolve' => function ($value, $args, $context, ResolveInfo $info) {
                return $this->filterResolverArraySection($value, $args, $context, $info);
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
