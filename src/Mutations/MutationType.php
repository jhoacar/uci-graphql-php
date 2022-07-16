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
     * Singleton Pattern.
     * @param array $mutationFields
     * @param array $mutationFieldsForbidden
     * @return MutationType
     */
    public static function mutation(array $mutationFields, array $mutationFieldsForbidden)
    {
        return self::$mutation === null ? (self::$mutation = new self($mutationFields, $mutationFieldsForbidden)) : self::$mutation;
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
            'name' => 'Mutation',
            'fields' => [
                ...$this->fields, ...$customFields,
            ],
            'resolveField' => function ($val, $args, $context, ResolveInfo $info) {
                return $this->{$info->fieldName}($val, $args, $context, $info);
            },
        ];
        parent::__construct($config);
    }
}
