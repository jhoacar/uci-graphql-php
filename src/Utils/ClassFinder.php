<?php

declare(strict_types=1);

namespace UciGraphQL\Utils;

use Throwable;

/**
 * * Represents a finder for classes that there are in a specified directory and a specific namespace.
 */
class ClassFinder
{
    /**
     * Search all classes defined in the namespace using autoloading
     * based in psr-4 standard, only serach in the directory and a level
     * for subdirectories.
     * @param string $composerDir This value should be the directory that contains composer.json. Important: must end in '/'
     * @param string $namespace The namespace to search
     * @param string $autoloaderSection Default autoload, can be other section autoloader loaded in composer.json, for example 'autoload-dev'
     * @param string $psr Default psr-4, can be other standard loaded in composer.json
     * @return array
     * @throws Throwable
     */
    public static function getClassesInNamespace(string $composerDir, string $namespace, string $autoloaderSection = 'autoload', string $psr = 'psr-4'): array
    {
        $directory = self::getNamespaceDirectory($composerDir, $namespace, $autoloaderSection, $psr);

        if (!strlen($directory)) {
            return [];
        }

        $files = self::listAllFiles($directory);

        $classes = [];
        foreach ($files as $file) {
            if (str_contains($file, '.php')) {
                $className = $namespace . '\\' . str_replace('.php', '', $file);
                array_push($classes, $className);
            }
        }

        return array_filter($classes, function ($possibleClass) {
            return class_exists($possibleClass);
        });
    }

    /**
     * Return the standard autoload psr-4 definition in composer.json.
     * @param string $composerDir
     * @param string $autoloaderSection Default autoload, can be other section autoloader loaded in composer.json, for example 'autoload-dev'
     * @param string $psr Default psr-4, can be other standard loaded in composer.json
     * @return array
     * @throws Throwable
     */
    private static function getDefinedNamespaces(string $composerDir, string $autoloaderSection, string $psr): array
    {
        $composerJsonPath = $composerDir . 'composer.json';
        $fileContent = file_get_contents($composerJsonPath);

        if ($fileContent === false) {
            $fileContent = '';
        }

        $composerConfig = (object) json_decode($fileContent);

        if (property_exists($composerConfig, $autoloaderSection)) {
            if (property_exists($composerConfig->$autoloaderSection, $psr)) {
                return (array) $composerConfig->$autoloaderSection->$psr;
            }

            return [];
        }

        return [];
    }

    /**
     * Returns the namespace directory if it exists or false otherwise.
     * @param string $composerDir
     * @param string $namespace
     * @param string $autoloaderSection Default autoload, can be other section autoloader loaded in composer.json, for example 'autoload-dev'
     * @param string $psr Default psr-4, can be other standard loaded in composer.json
     * @return string
     */
    private static function getNamespaceDirectory(string $composerDir, string $namespace, string $autoloaderSection, string $psr): string
    {
        $composerNamespaces = self::getDefinedNamespaces($composerDir, $autoloaderSection, $psr);

        $namespaceFragments = explode('\\', $namespace);

        $undefinedNamespaceFragments = [];

        while ($namespaceFragments) {
            $possibleNamespace = implode('\\', $namespaceFragments) . '\\';

            if (array_key_exists($possibleNamespace, $composerNamespaces)) {
                $realpath = realpath($composerDir . $composerNamespaces[$possibleNamespace] . implode('/', $undefinedNamespaceFragments));

                if (!$realpath) {
                    return '';
                }

                return $realpath;
            }

            array_unshift($undefinedNamespaceFragments, array_pop($namespaceFragments));
        }

        return '';
    }

    /**
     * A simple recursive function to list all files and subdirectories in a directory.
     * Only return the name of the files in relative path.
     * @param string $directory
     * @return array
     */
    private static function listAllFiles(string $directory)
    {
        $scandir = scandir($directory);
        if (!$scandir) {
            $scandir = [];
        }

        $array = array_diff($scandir, ['.', '..']);

        foreach ($array as $item) {
            if (is_dir($directory . $item)) {
                $array = array_merge($array, self::listAllFiles($item . DIRECTORY_SEPARATOR));
            }
        }

        return $array;
    }
}
