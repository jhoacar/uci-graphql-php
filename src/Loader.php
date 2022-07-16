<?php

declare(strict_types=1);

namespace UciGraphQL;

use UciGraphQL\Utils\ClassFinder;

/**
 * Trait (class) used by dependency injection
 * to search for classes found in the namespace.
 */
trait Loader
{
    /**
     * @var string
     */
    private static $method = 'getFields';
    /**
     * @var string
     */
    private static $cleanMethod = 'clean';
    /**
     * @var string
     */
    private static $interface = ILoader::class;
    /**
     * @var array
     */
    private $uciFields = [];
    /**
     * @var array
     */
    private $classes = [];
    /**
     * @var string
     */
    protected static $namespace = __NAMESPACE__;

    /**
     * Validate if the class complete the specifications.
     * @param string $class
     * @return bool
     */
    private static function isCorrectClass($class): bool
    {
        $implementations = class_implements($class);
        if (!$implementations) {
            $implementations = [];
        }

        return in_array(self::$interface, $implementations, true) && method_exists($class, self::$method);
    }

    /**
     * Return the invoke method in that class.
     * @param string $class
     * @return array
     */
    private function getResultClass($class): array
    {
        $callable = [$class, self::$method];
        if (is_callable($callable)) {
            return (array) call_user_func($callable);
        }

        return [];
    }

    /**
     * This function load all classes using this namespace,
     * Call each one using specific method
     * Load $fields and $classes attributes with his information.
     * @return void
     */
    private function searchFields(): void
    {
        $classes = ClassFinder::getClassesInNamespace(__DIR__ . '/../', self::$namespace);

        foreach ($classes as $class) {
            if (self::isCorrectClass($class)) {
                $result = $this->getResultClass($class);

                foreach ($result as $key => $value) {
                    $this->uciFields[$key] = $value;
                    $this->classes[$key] = $class;
                }
            }
        }
    }

    /**
     * Clean all fields.
     */
    private static function cleanFields():void
    {
        $classes = ClassFinder::getClassesInNamespace(__DIR__ . '/../', self::$namespace);

        foreach ($classes as $class) {
            if (self::isCorrectClass($class)) {
                self::cleanField($class);
            }
        }
    }

    /**
     * Return the invoke clean method in that class.
     * @param string $class
     * @return void
     */
    private static function cleanField($class): void
    {
        $callable = [$class, self::$cleanMethod];
        if (is_callable($callable)) {
            call_user_func($callable);
        }
    }
}
