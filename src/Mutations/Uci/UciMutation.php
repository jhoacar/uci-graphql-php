<?php

declare(strict_types=1);

namespace UciGraphQL\Mutations\Uci;

use UciGraphQL\ILoader;

/**
 * Class used for load all the uci type in GraphQL.
 */
class UciMutation implements ILoader
{
    /**
     * @var UciMutationType|null
     */
    private static $uci = null;

    /**
     * @return UciMutationType
     */
    public static function uci(): UciMutationType
    {
        return self::$uci === null ? (self::$uci = new UciMutationType()) : self::$uci;
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
