<?php

declare(strict_types=1);

namespace UciGraphQL\Mutations\Uci;

use GraphQL\Type\Definition\ResolveInfo;
use UciGraphQL\Providers\UciCommandProvider;
use UciGraphQL\Providers\UciProvider;
use UciGraphQL\Types\UciType;

/**
 * Class used for load all schema for the UCI System in GraphQL.
 */
class UciMutationType extends UciType
{
    /**
     * @param array $forbiddenConfigurations
     * @param UciProvider|null $provider
     * Construct all the type with dinamyc schema from the UCI System.
     */
    public function __construct($forbiddenConfigurations = [], $provider = null)
    {
        $this->provider = $provider === null ? new UciCommandProvider() : $provider;
        $this->forbiddenConfigurations = $forbiddenConfigurations;

        $config = [
            'name' => 'mutation_uci',
            'description' => 'Mutation for the Router Configuration',
            'fields' => $this->getUciFields(),
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
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

    /*
     * @inheritdoc
     */
    public function getOptionName($configName, $sectionName, $optionName): string
    {
        return 'mutation_' . $configName . '_' . $sectionName . '_' . $optionName;
    }
}
