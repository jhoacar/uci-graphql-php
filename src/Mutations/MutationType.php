<?php

declare(strict_types=1);

namespace UciGraphQL\Mutations;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use UciGraphQL\Loader;
use UciGraphQL\Mutations\Uci\UciMutation;
use UciGraphQL\Utils\ArrayMerge;

class MutationType extends ObjectType
{
    /*
     * This trait load all fields in each folder for this namespace
     */
    use Loader;

    /**
     * @var MutationType|null
     */
    private static $mutation = null;

    /**
     * Singleton Pattern.
     * @param array $customFields
     * @return MutationType
     */
    public static function mutation($customFields = []): self
    {
        return self::$mutation === null ? (self::$mutation = new self($customFields)) : self::$mutation;
    }

    /**
     * Clean the mutation builded.
     */
    public static function clean():void
    {
        // self::$namespace = __NAMESPACE__;
        // self::cleanFields();
        UciMutation::clean();
        self::$mutation = null;
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
            'name' => 'Mutation',
            'fields' =>  ArrayMerge::merge_arrays(UciMutation::getFields(), $customFields),
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
