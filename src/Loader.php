<?php

declare(strict_types=1);

namespace UciGraphQL;

use UciGraphQL\Utils\ClassFinder;

/**
 * Contract to load all the fields in GraphQL.
 */
interface ILoader
{
    /**
     * Returns all fields for GraphQL for each implementation.
     * @return array
     */
    public static function getFields(): array;
}

/**
 * Trait (class) used by dependency injection
 * to search for classes found in the namespace.
 */
trait Loader
{
    /**
     * @var string
     */
    private $method = 'getFields';
    /**
     * @var string
     */
    private $interface = ILoader::class;
    /**
     * @var array
     */
    private $fields = [];
    /**
     * @var array
     */
    private $classes = [];
    /**
     * @var string
     */
    protected $namespace = __NAMESPACE__;

    /**
     * Validate if the class complete the specifications.
     * @param string $class
     * @return bool
     */
    private function isCorrectClass(string $class): bool
    {
        $implementations = class_implements($class);
        if (!$implementations) {
            $implementations = [];
        }

        return in_array($this->interface, $implementations, true) && method_exists($class, $this->method);
    }

    /**
     * Return the invoke method in that class.
     * @param string $class
     * @return array
     */
    private function getResultClass(string $class): array
    {
        $callable = [$class, $this->method];
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
        $classes = ClassFinder::getClassesInNamespace(__DIR__ . '/../', $this->namespace);

        foreach ($classes as $class) {
            if ($this->isCorrectClass($class)) {
                $result = $this->getResultClass($class);

                foreach ($result as $key => $value) {
                    $this->fields[$key] = $value;
                    $this->classes[$key] = $class;
                }
            }
        }
    }
}