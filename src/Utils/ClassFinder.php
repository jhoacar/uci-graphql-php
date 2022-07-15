<?php

namespace UciGraphQL\Utils;

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
     * @return array
     */
    public static function getClassesInNamespace(string $composerDir, string $namespace): array
    {
        $directory = self::getNamespaceDirectory($composerDir, $namespace);

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
     * @return array
     */
    private static function getDefinedNamespaces(string $composerDir): array
    {
        $composerJsonPath = $composerDir . 'composer.json';
        $fileContent = file_get_contents($composerJsonPath);
        if (!$fileContent) {
            $fileContent = '';
        }
        $composerConfig = (object) json_decode($fileContent);

        if (property_exists($composerConfig, 'autoload')) {
            if (property_exists($composerConfig->autoload, 'psr-4')) {
                return (array) $composerConfig->autoload->{'psr-4'};
            }

            return [];
        }

        return [];
    }

    /**
     * Returns the namespace directory if it exists or false otherwise.
     * @param string $composerDir
     * @param string $namespace
     * @return string
     */
    private static function getNamespaceDirectory($composerDir, $namespace): string
    {
        $composerNamespaces = self::getDefinedNamespaces($composerDir);

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

        foreach ($array as &$item) {
            $item = $directory . $item;
        }
        unset($item);
        foreach ($array as $item) {
            if (is_dir($item)) {
                $array = array_merge($array, self::listAllFiles($item . DIRECTORY_SEPARATOR));
            }
        }

        return $array;
    }
}
