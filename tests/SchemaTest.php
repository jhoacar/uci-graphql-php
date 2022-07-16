<?php

declare(strict_types=1);

namespace UciGraphQL\Tests;

use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;
use UciGraphQL\Schema;

class SchemaTest extends TestCase
{
    /**
     * @return void
     */
    public function testLoadCorrectSchema() :void
    {
        Schema::clean();
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
        $result = (array) GraphQL::executeQuery(Schema::get(), $query)->toArray();

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
