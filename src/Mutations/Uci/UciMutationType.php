<?php

declare(strict_types=1);

namespace UciGraphQL\Mutations\Uci;

use Context;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use UciGraphQL\Providers\ACTIONS;
use UciGraphQL\Providers\UciCommandProvider;
use UciGraphQL\Providers\UciProvider;
use UciGraphQL\Types\UciType;

/**
 * Class used for load all schema for the UCI System in GraphQL.
 */
class UciMutationType extends UciType
{
    /**
     * @var EnumType
     */
    private $actionEnum;

    /**
     * @param array $forbiddenConfigurations
     * @param UciProvider|null $provider
     * Construct all the type with dinamyc schema from the UCI System.
     */
    public function __construct($forbiddenConfigurations = [], $provider = null)
    {
        $this->actionEnum = new EnumType([
            'name' => 'action_mutations',
            'description' => 'Actions for the options',
            'values' => [
                'SET' => [
                    'value' => ACTIONS::SET,
                    'description' => 'Set the value of the given option',
                ],
                'DELETE' => [
                    'value' => ACTIONS::DELETE,
                    'description' => 'Delete the given option.',
                ],
                'RENAME' => [
                    'value' => ACTIONS::RENAME,
                    'description' => 'Rename the given option to the given name.',
                ],
                'ADD_LIST' =>[
                    'value' => ACTIONS::ADD_LIST,
                    'description' => 'Add the given string to an existing list option.',
                ],
                'DEL_LIST' =>[
                    'value' => ACTIONS::DEL_LIST,
                    'description' => 'Remove the given string from an existing list option.',
                ],
                'REVERT' => [
                    'value' => ACTIONS::REVERT,
                    'description' => 'Revert the given option',
                ],
            ],
        ]);
        $this->provider = $provider === null ? new UciCommandProvider() : $provider;
        $this->forbiddenConfigurations = $forbiddenConfigurations;

        $config = [
            'name' => 'mutation_uci',
            'description' => 'Mutation for the Router Configuration',
            'fields' => $this->getUciFields(),
            'resolveField' => function ($value, $args, Context $context, ResolveInfo $info) {
                return $this->uciInfo[$info->fieldName];
            },
        ];
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function getConfigName($configName): string
    {
        return 'mutation_' . $configName;
    }

    /**
     * @inheritdoc
     */
    public function getSectionName($configName, $sectionName): string
    {
        return 'mutation_' . $configName . '_' . $sectionName;
    }

    /**
     * @inheritdoc
     */
    public function getOptionName($configName, $sectionName, $optionName): string
    {
        return 'mutation_' . $configName . '_' . $sectionName . '_' . $optionName;
    }

    /**
     * @inheritdoc
     */
    protected function getSectionType($configName, $sectionName, $sectionFields, $isArray): array
    {
        $arguments = array_map(function ($argument) use ($configName, $sectionName) {
            return [
                $argument => [
                    'type' => Type::string(),
                    'description' => "$argument to search in the $sectionName section in $configName configuration of the UCI System",
                ],
            ];
        }, array_keys($sectionFields));

        $configArray = [
            'name' => $sectionName,
            'description' => $this->getsectionDescription($sectionName, $configName),
            'args' => array_merge_recursive($arguments, [
                'index' => [
                    'type' => Type::int(),
                    'description' => "Index of the array in the $sectionName section in $configName configuration of the UCI System",
                ],
            ]),
            'type' => Type::listOf($this->getUniqueSectionType($configName, $sectionName, $sectionFields)),
            'resolve' => function ($value, $args, Context $context, ResolveInfo $info) {
                $context->isArraySection = true;
                if (isset($args['index']) && !empty($value[$info->fieldName])) {
                    $context->indexSection = (int) $args['index'];

                    return array_slice((array) $value[$info->fieldName], (int) $args['index'], (int) $args['index'] + 1);
                } elseif (!empty($value[$info->fieldName]) && is_array($value[$info->fieldName])) {
                    return array_filter($value[$info->fieldName], function ($section) use ($args) {
                        if (empty($args)) {
                            return true;
                        }
                        $match = false;
                        foreach ($args as $arg => $value) {
                            if (isset($section[$arg]) && $section[$arg] === $value) {
                                $match = true;
                            }
                        }

                        return $match;
                    });
                } else {
                    return null;
                }
            },
        ];

        return $isArray ? $configArray : [
            'description' => $this->getsectionDescription($sectionName, $configName),
            'type' => $this->getUniqueSectionType($configName, $sectionName, $sectionFields),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getOptionType($configName, $sectionName, $optionName): array
    {
        return [
            'name' => $optionName,
            'args' => [
                'action' => [
                    'type' => $this->actionEnum,
                    'description' => 'Action to execute in this option',
                ],
                'value' => [
                    'type' => Type::string(),
                    'description' => 'Value to load in the action',
                ],
            ],
            'description' => $this->getOptionDescription($optionName, $sectionName, $configName),
            'type' => Type::listOf(Type::string()),
            'resolve' => function ($value, $args, Context $context, ResolveInfo $info) {
                [$uci,$config,$section,$option] = $info->path;
            // return $this->provider->dispatchAction($args['action'], $config, $section, $option, $args['value']);
            },
        ];
    }
}
