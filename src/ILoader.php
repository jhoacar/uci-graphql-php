<?php

declare(strict_types=1);

namespace UciGraphQL;

/**
 * Contract to load all the fields in GraphQL.
 */
interface ILoader
{
    /**
     * Returns all fields for GraphQL for each implementation.
     * @return array
     */
    public static function getFields(): array;
}