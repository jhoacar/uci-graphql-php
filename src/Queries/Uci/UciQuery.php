<?php

declare(strict_types=1);

namespace UciGraphQL\Queries\Uci;

use UciGraphQL\ILoader;

/**
 * Class used for load all the uci type in GraphQL.
 */
class UciQuery implements ILoader
{
    /**
     * @var UciType
     */
    private static $uci;

    /**
     * @param array $fieldsForbidden
     * @return UciType
     */
    private static function uci(array $fieldsForbidden)
    {
        return self::$uci === null ? (self::$uci = new UciType($fieldsForbidden)) : self::$uci;
    }

    /**
     * @inheritdoc
     */
    public static function getFields(array $fieldsForbidden): array
    {
        return [
            'uci' => [
                'type' => self::uci($fieldsForbidden),
            ],
        ];
    }
}
