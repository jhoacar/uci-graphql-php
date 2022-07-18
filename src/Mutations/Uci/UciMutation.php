<?php

declare(strict_types=1);

namespace UciGraphQL\Mutations\Uci;

use UciGraphQL\ILoader;
use UciGraphQL\Providers\UciProvider;

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
     * @param array $forbiddenConfigurations
     * @param UciProvider|null $provider
     * @return UciMutationType
     */
    public static function uci($forbiddenConfigurations = [], $provider = null): UciMutationType
    {
        return self::$uci === null ? (self::$uci = new UciMutationType($forbiddenConfigurations, $provider)) : self::$uci;
    }

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
