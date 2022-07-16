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
     * @var Schema
     */
    private static $instance = null;

    /*
     * We use a private construct method for prevent instances
     * Its called as singleton pattern
    */
    private function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Return the global instance for the schema in GraphQL.
     * @param array $queryFields Default empty
     * @param array $queryFieldsForbidden Default empty
     * @param array $mutationFields Default empty
     * @param array $mutationFieldsForbidden Default empty
     * @return Schema
     */
    public static function get(array $queryFields = [], array $queryFieldsForbidden = [], array $mutationFields = [], array $mutationFieldsForbidden = []): self
    {
        return self::$instance === null ? (self::$instance = new self([
            'query' => QueryType::query($queryFields, $queryFieldsForbidden),
            'mutation' => MutationType::mutation($mutationFields, $mutationFieldsForbidden),
        ])) : self::$instance;
    }
}
