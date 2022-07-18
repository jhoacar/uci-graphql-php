<?php

declare(strict_types=1);

namespace UciGraphQL\Queries;

use Context;
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
     * @var QueryType|null
     */
    private static $query = null;

    /**
     * Return the global instance for the queries in GraphQL.
     * @param array $customFields
     * @return QueryType
     */
    public static function query($customFields = []): self
    {
        return self::$query === null ? (self::$query = new self($customFields)) : self::$query;
    }

    /**
     * Clean the query builded.
     */
    public static function clean():void
    {
        self::cleanFields();
        self::$query = null;
    }

    /**
     * We use a private construct method for prevent instances
     * Its called as singleton pattern.
     * @param array $customFields
     */
    private function __construct($customFields = [])
    {
        self::$namespace = __NAMESPACE__;
        $this->searchFields();

        $config = [
            'name' => 'Query',
            'fields' =>  array_merge_recursive($this->uciFields, $customFields),
            'resolveField' => function ($value, $args, Context $context, ResolveInfo $info) {
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
