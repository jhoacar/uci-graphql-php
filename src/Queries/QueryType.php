<?php

declare(strict_types=1);

namespace UciGraphQL\Queries;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use UciGraphQL\Loader;
use UciGraphQL\Utils\Command;

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
     * Custom fields.
     * @var array
     */
    private $customFields = [];

    /**
     * Return the global instance for the queries in GraphQL.
     * @return QueryType
     */
    public static function query()
    {
        return self::$query === null ? (self::$query = new self()) : self::$query;
    }

    /**
     * We use a private construct method for prevent instances
     * Its called as singleton pattern.
     */
    private function __construct()
    {
        $this->namespace = __NAMESPACE__;
        $this->searchFields();

        $this->customFields = [
            'whoami' => [
                'type' => Type::string(),
                'resolve' => fn () => Command::execute('whoami'),
            ],
        ];

        $config = [
            'name' => 'Query',
            'fields' =>  [
                ...$this->customFields, ...$this->fields,
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
