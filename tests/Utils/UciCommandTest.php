<?php

declare(strict_types=1);

namespace UciGraphQL\Tests\Utils;

use PHPUnit\Framework\TestCase;
use UciGraphQL\Providers\ACTIONS;
use UciGraphQL\Providers\UciCommandProvider;
use UciGraphQL\Providers\UciSection;

class UciCommandDump extends UciCommandProvider
{
    /**
     * @var string
     */
    public static string $uciOutputCommand;

    /**
     * @inheritdoc
     */
    public static function getConfigurationCommand(): array
    {
        return explode(PHP_EOL, self::$uciOutputCommand);
    }

    /**
     * @inheritdoc
     */
    public function dispatchAction($action, $config, $section, $indexSection, $option, $value): array
    {
        $result = [];

        $section = self::getUciConfiguration()[$config][$section];
        if ($indexSection === parent::IS_OBJECT_SECTION && $section instanceof UciSection) {
            $result = $section->options[$option];
        } elseif (is_array($section) && $indexSection >= 0) {
            $result = $section[$indexSection][$option];
        }

        switch ($action) {
            case ACTIONS::SET:
                return array_merge($result, [$value]);
        }

        return [$value];
    }
}

final class UciCommandTest extends TestCase
{
    /**
     * @return iterable<array{input: string, expectations: array}>
     */
    public function uciConfigDataProvider(): iterable
    {
        yield 'allConfig' => [
            'input' => require realpath(__DIR__ . '/../UciResult.php'),
            'expectations' => [
                [
                    'assert' => 'assertArrayHasKey',
                    'key' => 'network',
                    'message' => 'Must contain network field',
                ],
                [
                    'assert' => 'assertIsArray',
                    'config' => 'firewall',
                    'section' => 'rule',
                    'message' => 'Must be an array firewall.rule',
                ],
                [
                    'assert' => 'assertIsObject',
                    'config' => 'network',
                    'section' => 'wan',
                    'message' => 'Must be an instance of ' . UciSection::class . ' network.wan',
                ],
            ],
        ];
    }

    /**
     * @dataProvider uciConfigDataProvider
     * @return void
     */
    public function testLoadAllConfigUciSystem(string $input, array $expectations)
    {
        UciCommandDump::$uciOutputCommand = $input;
        $result = UciCommandDump::getUciConfiguration();

        foreach ($expectations as $expected) {
            switch ($expected['assert']) {
                case 'assertArrayHasKey':
                    self::assertArrayHasKey($expected['key'], $result, $expected['message']);
                    continue 2;
                case 'assertIsArray':
                    self::assertIsArray($result[$expected['config']][$expected['section']], $expected['message']);
                    continue 2;
                case 'assertIsObject':
                    self::assertInstanceOf(UciSection::class, $result[$expected['config']][$expected['section']], $expected['message']);
                    continue 2;
            }
        }
    }
}
