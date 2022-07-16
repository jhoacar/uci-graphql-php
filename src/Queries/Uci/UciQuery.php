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
     * @return UciType
     */
    private static function uci()
    {
        return self::$uci === null ? (self::$uci = new UciType()) : self::$uci;
    }

    public static function getFields(): array
    {
        return [
            'uci' => [
                'type' => self::uci(),
            ],
        ];
    }
}
