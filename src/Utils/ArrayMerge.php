<?php

namespace UciGraphQL\Utils;

class ArrayMerge
{
    /**
     * Recursive function that merges two associative arrays
     * - Unlike array_merge_recursive, a differing value for a key
     *   overwrites that key rather than creating an array with both values
     * - A scalar value will overwrite an array value.
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public static function merge_arrays($arr1, $arr2): array
    {
        $keys = array_keys($arr2);
        foreach ($keys as $key) {
            if (
                isset($arr1[$key])
                && is_array($arr1[$key])
                && is_array($arr2[$key])
            ) {
                $arr1[$key] = self::merge_arrays($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $arr2[$key];
            }
        }

        return $arr1;
    }
}
