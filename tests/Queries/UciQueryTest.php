<?php

declare(strict_types=1);

namespace UciGraphQL\Tests\Queries;

use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;
use UciGraphQL\Queries\Uci\UciType;
use UciGraphQL\Schema;
use UciGraphQL\Tests\Utils\UciCommandDump;

class UciQueryTest extends TestCase
{
    public function testLoadAllQueries(): void
    {
        UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');
        UciType::$commandExecutor = new UciCommandDump();
        $query = '
            {
                __type(name: "uci") {
                    name
                    fields {
                      name
                      type {
                        name
                      }
                    }
                  }
            }           
            ';
        $result = (array) GraphQL::executeQuery(Schema::get(), $query)->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertArrayNotHasKey('errors', $result);

        $data = (array) $result['data'];
        self::assertArrayHasKey('__type', $data);

        $uciType = (array) $data['__type'];
        self::assertArrayHasKey('fields', $uciType);

        $fields = (array) $uciType['fields'];
        self::assertIsArray($fields);
        self::assertTrue(count($fields) > 0);
    }
}
