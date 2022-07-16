<?php

declare(strict_types=1);

namespace UciGraphQL\Mutations;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use UciGraphQL\Loader;

class MutationType extends ObjectType
{
    /*
     * This trait load all fields in each folder for this namespace
     */
    use Loader;

    /**
     * @var MutationType
     */
    private static $mutation = null;

    /**
     * @var array
     */
    public static $customFields = [];

    /**
     * Singleton Pattern.
     * @return MutationType
     */
    public static function mutation(): self
    {
        return self::$mutation === null ? (self::$mutation = new self()) : self::$mutation;
    }

    /**
     * We use a private construct method for prevent instances
     * Its called as singleton pattern.
     */
    private function __construct()
    {
        $this->namespace = __NAMESPACE__;
        $this->searchFields();
        $config = [
            'name' => 'Mutation',
            'fields' => [
                ...$this->uciFields, ...self::$customFields,
            ],
            'resolveField' => function ($val, $args, $context, ResolveInfo $info) {
                return $this->{$info->fieldName}($val, $args, $context, $info);
            },
        ];
        parent::__construct($config);
    }
}
