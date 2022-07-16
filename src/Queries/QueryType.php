<?php

declare(strict_types=1);

namespace UciGraphQL\Queries;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use UciGraphQL\Loader;

/**
 * Class used for the global queries in GraphQL.
 */
class QueryType extends ObjectType
{
    /*
     * This trait load all fields in each folder for this namespace
     */
    use Loader;

    /**
     * Global instance in all the aplication.
     * @var QueryType
     */
    private static $query = null;

    /**
     * Return the global instance for the queries in GraphQL.
     * @param array $queryFields
     * @param array $queryFieldsForbidden
     * @return QueryType
     */
    public static function query(array $queryFields, array $queryFieldsForbidden)
    {
        return self::$query === null ? (self::$query = new self($queryFields, $queryFieldsForbidden)) : self::$query;
    }

    /**
     * We use a private construct method for prevent instances
     * Its called as singleton pattern.
     * @param array $customFields If an custom field match with the defined, its override
     * @param array $fieldsForbidden If match any field so it isn't loaded in the schema
     */
    private function __construct(array $customFields, array $fieldsForbidden)
    {
        $this->fieldsForbidden = $fieldsForbidden;
        $this->namespace = __NAMESPACE__;
        $this->searchFields();

        $config = [
            'name' => 'Query',
            'fields' =>  [
                ...$this->uciFields, ...$customFields,
            ],
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                /**
                 * Execute this function load the root value for the fields
                 * If a method in this class has the name 'resolve' . $fieldName
                 * is called for resolve, empty string for the root value otherwise.
                 */
                $method = 'resolve' . ucfirst($info->fieldName);
                if (method_exists($this, $method)) {
                    return $this->{$method}($value, $args, $context, $info);
                } else {
                    return '';
                }
            },
        ];
        parent::__construct($config);
    }
}
