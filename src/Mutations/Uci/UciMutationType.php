<?php

declare(strict_types=1);

namespace UciGraphQL\Mutations\Uci;

use GraphQL\Type\Definition\ResolveInfo;
use UciGraphQL\Types\UciType;
use UciGraphQL\Utils\UciCommand;

/**
 * Class used for load all schema for the UCI System in GraphQL.
 */
class UciMutationType extends UciType
{
    public function __construct()
    {
        if (self::$commandExecutor === null) {
            self::$commandExecutor = new UciCommand();
        }

        $config = [
            'name' => 'mutation_uci',
            'description' => 'Router Configuration',
            'fields' => $this->getUciFields(),
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                return $this->uciInfo[$info->fieldName];
            },
        ];
        parent::__construct($config);
    }
}
