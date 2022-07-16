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
     * @var UciType|null
     */
    private static $uci = null;

    /**
     * @return UciType
     */
    public static function uci(): UciType
    {
        return self::$uci === null ? (self::$uci = new UciType()) : self::$uci;
    }

    /**
     * @inheritdoc
     */
    public static function getFields(): array
    {
        return [
            'uci' => [
                'type' => self::uci(),
            ],
        ];
    }

    /**
     * Clean all fields for UCI System.
     */
    public static function clean(): void
    {
        self::$uci = null;
    }
}
