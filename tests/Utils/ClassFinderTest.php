<?php

declare(strict_types=1);

namespace UciGraphQL\Tests\Utils;

use PHPUnit\Framework\TestCase;
use UciGraphQL\Queries\QueryType;
use UciGraphQL\Schema;
use UciGraphQL\Utils\ClassFinder;

final class ClassFinderTest extends TestCase
{
    /**
     * @return iterable<array{params: array, expected: array}>
     */
    public function classFinderDataProvider(): iterable
    {
        yield 'currentNamespace' => [
            'params' => [
                'composerDir' => __DIR__ . '/../../',
                'namespace' => __NAMESPACE__,
                'autoloaderSection' => 'autoload-dev',
                'psr' => 'psr-4',
            ],
            'expected' => [
                'assert' => 'assertContains',
                'needle' => self::class,
                'message' => 'Array must contain the ' . self::class . ' class',
            ],
        ];

        yield 'schemaNamespace' => [
            'params' => [
                'composerDir' => __DIR__ . '/../../',
                'namespace' => 'UciGraphQL',
                'autoloaderSection' => 'autoload',
                'psr' => 'psr-4',
            ],
            'expected' => [
                'assert' => 'assertContains',
                'needle' => Schema::class,
                'message' => 'Array must contain the ' . Schema::class . ' class',
            ],
        ];

        yield 'queriesNamespace' => [
            'params' => [
                'composerDir' => __DIR__ . '/../../',
                'namespace' => 'UciGraphQL\\Queries',
                'autoloaderSection' => 'autoload',
                'psr' => 'psr-4',
            ],
            'expected' => [
                'assert' => 'assertContains',
                'needle' => QueryType::class,
                'message' => 'Array must contain the ' . QueryType::class . ' class',
            ],
        ];
    }

    /**
     * @dataProvider classFinderDataProvider
     * @return void
     */
    public function testLoadAllClassInNamespace(array $params, array $expected)
    {
        $composerDir = $params['composerDir'];
        $namespace = $params['namespace'];
        $autoloaderSection = $params['autoloaderSection'];
        $psr = $params['psr'];
        $classes = ClassFinder::getClassesInNamespace($composerDir, $namespace, $autoloaderSection, $psr);

        if ($expected['assert'] === 'assertContains') {
            self::assertContains($expected['needle'], $classes, $expected['message']);
        }
    }
}
