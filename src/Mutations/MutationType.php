<?php

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
     * Singleton Pattern.
     * @return MutationType
     */
    public static function mutation()
    {
        return self::$mutation === null ? (self::$mutation = new self()) : self::$mutation;
    }

    /*************** Singleton Pattern **************/
    private function __construct()
    {
        $this->namespace = __NAMESPACE__;
        $this->searchFields();
        $config = [
            'name' => 'Mutation',
            'fields' => [],
            'resolveField' => function ($val, $args, $context, ResolveInfo $info) {
                return $this->{$info->fieldName}($val, $args, $context, $info);
            },
        ];
        parent::__construct($config);
    }
}
