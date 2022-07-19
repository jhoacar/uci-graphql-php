<?php

declare(strict_types=1);

namespace UciGraphQL\Tests\Queries;

use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;
use UciGraphQL\Mutations\Uci\UciMutation;
use UciGraphQL\Queries\Uci\UciQuery;
use UciGraphQL\Schema;
use UciGraphQL\Tests\Utils\UciCommandDump;

class UciQueryTest extends TestCase
{
    public function testLoadAllQueries(): void
    {
        Schema::clean();
        UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');
        UciMutation::uci([], new UciCommandDump());
        UciQuery::uci([], new UciCommandDump());

        $query = '
            {
                __type(name: "query_uci") {
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
        $result = (array) GraphQL::executeQuery(Schema::get(), $query)->toArray(true);

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertArrayNotHasKey('errors', $result);

        $data = (array) $result['data'];
        self::assertArrayHasKey('__type', $data);

        $uciType = (array) $data['__type'];
        self::assertArrayHasKey('fields', $uciType);

        $fields = (array) $uciType['fields'];
        self::assertIsArray($fields);
        self::assertTrue(count($fields) > 0, 'Query uci need a field');
    }

    public function testHideQueryForbiddenFields(): void
    {
        Schema::clean();
        UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');

        $forbiddenConfigurations = [
          'network' => [
            'loopback' => [
              'proto',
            ],
          ],
        ];
        UciQuery::uci($forbiddenConfigurations, new UciCommandDump());
        UciMutation::uci([], new UciCommandDump());
        $query = '
            {
                uci{
                  network{
                    loopback{
                      proto
                    }
                  }
                }
            }           
            ';
        $result = (array) GraphQL::executeQuery(Schema::get(), $query)->toArray(true);

        self::assertIsArray($result);
        self::assertArrayNotHasKey('data', $result);
        self::assertArrayHasKey('errors', $result);

        $errors = (array) $result['errors'];
        self::assertIsArray($errors);
        self::assertTrue(count($errors) > 0);

        $firstPosition = (array) $errors[0];
        $message = $firstPosition['message'];
        self::assertIsString($message);
        if (is_string($message)) {
            self::assertTrue(str_contains($message, 'Cannot query field'));
        }
    }

    public function testLoadAllOptionsSection():void
    {
        Schema::clean();
        UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');
        UciMutation::uci([], new UciCommandDump());
        UciQuery::uci([], new UciCommandDump());
        $query = '
            {
              uci{    
                dhcp{
                  lan{start}
                  wan{start}
                }
              }
            }           
            ';
        $result = (array) GraphQL::executeQuery(Schema::get(), $query)->toArray(true);

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertArrayNotHasKey('errors', $result);

        $data = (array) $result['data'];
        self::assertIsArray($data);
        self::assertArrayHasKey('uci', $data);

        $uci = (array) $data['uci'];
        self::assertIsArray($uci);
        self::assertArrayHasKey('dhcp', $uci);

        $dhcp = (array) $uci['dhcp'];
        self::assertIsArray($dhcp);
        self::assertArrayHasKey('lan', $dhcp);
        self::assertArrayHasKey('wan', $dhcp);

        $lan = (array) $dhcp['lan'];
        self::assertIsArray($lan);
        self::assertArrayHasKey('start', $lan);

        $wan = (array) $dhcp['wan'];
        self::assertIsArray($wan);
        self::assertArrayHasKey('start', $wan);
    }
}
