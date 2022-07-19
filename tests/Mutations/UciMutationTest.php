<?php

declare(strict_types=1);

namespace UciGraphQL\Tests\Mutations;

use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;
use UciGraphQL\Mutations\Uci\UciMutation;
use UciGraphQL\Queries\Uci\UciQuery;
use UciGraphQL\Schema;
use UciGraphQL\Tests\Utils\UciCommandDump;

class UciMutationTest extends TestCase
{
    public function testLoadAllMutations(): void
    {
        Schema::clean();
        UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');
        UciMutation::uci([], new UciCommandDump());
        UciQuery::uci([], new UciCommandDump());

        $mutation = '
            {
                __type(name: "mutation_uci") {
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
        $result = (array) GraphQL::executeQuery(Schema::get(), $mutation)->toArray(true);

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

    public function testHideMutationForbiddenFields(): void
    {
        Schema::clean();
        UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');

        $queryForbidden = [
          'network' => [
            'loopback' => [
              'proto',
            ],
          ],
        ];
        $mutationForbidden = [
          'firewall' => true,
        ];

        UciQuery::uci($queryForbidden, new UciCommandDump());
        UciMutation::uci($mutationForbidden, new UciCommandDump());

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

        $mutation = '
        mutation {
          uci{
            firewall
          }
        }           
        ';
        $result = (array) GraphQL::executeQuery(Schema::get(), $mutation)->toArray(true);

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

    public function testLoadSetArgMutations(): void
    {
        Schema::clean();
        UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');
        UciMutation::uci([], new UciCommandDump());
        UciQuery::uci([], new UciCommandDump());

        $mutation = '
        mutation{
          uci{
            network{
              loopback{
                proto(action: SET, value: "otro")
              }
            }
          }
        }';
        $result = (array) GraphQL::executeQuery(Schema::get(), $mutation)->toArray(true);

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertArrayNotHasKey('errors', $result);

        $data = (array) $result['data'];
        self::assertIsArray($data);
        self::assertArrayHasKey('uci', $data);

        $uci = (array) $data['uci'];
        self::assertIsArray($uci);
        self::assertArrayHasKey('network', $uci);

        $network = (array) $uci['network'];
        self::assertIsArray($network);
        self::assertArrayHasKey('loopback', $network);

        $loopback = (array) $network['loopback'];
        self::assertIsArray($loopback);
        self::assertArrayHasKey('proto', $loopback);

        $proto = (array) $loopback['proto'];
        self::assertIsArray($proto);
        self::assertContains('otro', $proto);
    }

    public function testFilterSection():void
    {
        Schema::clean();
        UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');
        UciMutation::uci([], new UciCommandDump());
        UciQuery::uci([], new UciCommandDump());

        $mutation = '
        mutation {
          uci{
            firewall{
              rule(name: "/(Allow)/i"){
                name(action: SET, value: "other name")
              }
            }
          }   
        }
        ';
        $result = (array) GraphQL::executeQuery(Schema::get(), $mutation)->toArray(true);

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertArrayNotHasKey('errors', $result);

        $data = (array) $result['data'];
        self::assertIsArray($data);
        self::assertArrayHasKey('uci', $data);

        $uci = (array) $data['uci'];
        self::assertIsArray($uci);
        self::assertArrayHasKey('firewall', $uci);

        $firewall = (array) $uci['firewall'];
        self::assertIsArray($firewall);
        self::assertArrayHasKey('rule', $firewall);

        $rule = (array) $firewall['rule'];
        self::assertIsArray($rule);
        self::assertTrue(count($rule) > 0, 'Must have at least one rule');

        $firstRule = (array) $rule[0];
        self::assertIsArray($firstRule);
        self::assertArrayHasKey('name', $firstRule);

        $name = (array) $firstRule['name'];
        self::assertIsArray($name);
        self::assertContains('other name', $name);
    }
}
