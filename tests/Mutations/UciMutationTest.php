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

        $query = '
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

    public function testHideForbiddenFields(): void
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
        $result = (array) GraphQL::executeQuery(Schema::get(), $query)->toArray();

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
        $result = (array) GraphQL::executeQuery(Schema::get(), $mutation)->toArray();

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
}
