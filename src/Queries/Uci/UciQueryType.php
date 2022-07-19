<?php

declare(strict_types=1);

namespace UciGraphQL\Queries\Uci;

use GraphQL\Type\Definition\ResolveInfo;
use UciGraphQL\Providers\UciCommandProvider;
use UciGraphQL\Providers\UciProvider;
use UciGraphQL\Types\UciType;

/**
 * Class used for load all schema for the UCI System in GraphQL.
 */
class UciQueryType extends UciType
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
            'name' => 'query_uci',
            'description' => 'Query in the Router Configuration',
            'fields' => $this->getUciFields(),
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                return $this->uciInfo[$info->fieldName] ?? null;
            },
        ];
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function getConfigName($configName): string
    {
        return 'query_' . $configName;
    }

    /**
     * @inheritdoc
     */
    public function getSectionName($configName, $sectionName): string
    {
        return 'query_' . $configName . '_' . $sectionName;
    }

    /*
     * @inheritdoc
     */
    public function getOptionName($configName, $sectionName, $optionName): string
    {
        return 'query_' . $configName . '_' . $sectionName . $optionName;
    }
}
