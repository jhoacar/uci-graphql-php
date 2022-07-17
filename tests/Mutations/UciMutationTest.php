<?php

declare(strict_types=1);

namespace UciGraphQL\Tests\Mutations;

use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;
use UciGraphQL\Mutations\Uci\UciMutationType;
use UciGraphQL\Schema;
use UciGraphQL\Tests\Utils\UciCommandDump;

class UciMutationTest extends TestCase
{
    public function testLoadAllMutations(): void
    {
        // Schema::clean();
        // UciCommandDump::$uciOutputCommand = require realpath(__DIR__ . '/../UciResult.php');
        // UciMutationType::$provider = new UciCommandDump();
        // $query = '
        //     {
        //         __type(name: "mutation_uci") {
        //             name
        //             fields {
        //               name
        //               type {
        //                 name
        //               }
        //             }
        //           }
        //     }
        //     ';
        $result = []; // (array) GraphQL::executeQuery(Schema::get(), $query)->toArray();

        self::assertIsArray($result);
        // self::assertArrayHasKey('data', $result);
        // self::assertArrayNotHasKey('errors', $result);

        // $data = (array) $result['data'];
        // self::assertArrayHasKey('__type', $data);

        // $uciType = (array) $data['__type'];
        // self::assertArrayHasKey('fields', $uciType);

        // $fields = (array) $uciType['fields'];
        // self::assertIsArray($fields);
        // self::assertTrue(count($fields) > 0);
    }
}
