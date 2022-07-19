<?php

declare(strict_types=1);

namespace UciGraphQL;

use GraphQL\Type\Schema as BaseSchema;
use UciGraphQL\Mutations\MutationType;
use UciGraphQL\Queries\QueryType;

/**
 * Class used for the global schema in GraphQL.
 */
class Schema extends BaseSchema
{
    /**
     * Global instance in all the aplication.
     * @var Schema|null
     */
    public static $instance = null;

    /**
     * We use a private construct method for prevent instances
     * Its called as singleton pattern.
     * @param array $config
     */
    private function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Return the global instance for the schema in GraphQL.
     * @return Schema
     */
    public static function get(): self
    {
        return self::$instance === null ? (self::$instance = new self([
            'query' => QueryType::query(),
            'mutation' => MutationType::mutation(),
        ])) : self::$instance;
    }

    /**
     * Clean all schema loaded.
     */
    public static function clean(): void
    {
        QueryType::clean();
        MutationType::clean();
        self::$instance = null;
    }
}
