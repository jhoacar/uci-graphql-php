<?php

declare(strict_types=1);

namespace UciGraphQL\Tests;

use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;
use UciGraphQL\Mutations\Uci\UciMutation;
use UciGraphQL\Queries\Uci\UciQuery;
use UciGraphQL\Schema;
use UciGraphQL\Tests\Utils\UciCommandDump;

class SchemaTest extends TestCase
{
    /**
     * @return void
     */
    public function testLoadCorrectSchema() :void
    {
        Schema::clean();
        UciMutation::uci([], new UciCommandDump());
        UciQuery::uci([], new UciCommandDump());
        $query = '
            query IntrospectionQuery {
                __schema {
                  queryType {
                    name
                  }
                  mutationType{
                    name
                  }
                }
            }           
            ';
        $result = (array) GraphQL::executeQuery(Schema::get(), $query)->toArray(true);

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertArrayNotHasKey('errors', $result);

        $data = (array) $result['data'];
        self::assertArrayHasKey('__schema', $data);

        $schema = (array) $data['__schema'];
        self::assertArrayHasKey('queryType', $schema);
        self::assertArrayHasKey('mutationType', $schema);

        self::assertArrayHasKey('name', (array) $schema['queryType']);
        self::assertArrayHasKey('name', (array) $schema['mutationType']);
    }
}
