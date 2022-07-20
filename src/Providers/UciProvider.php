<?php

declare(strict_types=1);

namespace UciGraphQL\Providers;

/**
 * Represents each action in the uci information.
 */
abstract class ACTIONS
{
    const SET = 'set';
    const DELETE = 'delete';
    const RENAME = 'rename';
    const ADD_LIST = 'add_list';
    const DEL_LIST = 'del_list';
    const REVERT = 'revert';
}

/**
 * Represents the Section for the UCI System.
 */
class UciSection
{
    /**
     * @var array
     */
    public $options = [];
}

/**
 * Represents the information for the UCI System.
 */
abstract class UciProvider
{
    /**
     * This constant load all options in each section.
     */
    const ALL_INDEXES_SECTION = -5;
    /**
     * This constant is for represent that is an array.
     */
    const IS_OBJECT_SECTION = -10;

    /**
     * @var array
     */
    protected $services = [];

    /**
     * Return an object with the representation for the UCI System.
     *
     * For example:
     *  {
     *      app:{
     *          port
     *      }
     *  }
     *
     * @return array
     */
    abstract public static function getUciConfiguration(): array;

    /**
     * - If a section is an array is saved as a Array.
     *
     *      - This array is saved with the position described by the uci system
     *
     * - If a section is not an array, so it's saved as a UciSection
     *
     *      - This UciSection has an attribute 'options' for each option in this section
     *
     * @param array|UciSection &$configSection
     * @param string $sectionName
     * @param string $optionName
     * @param array $content
     * @return void
     */
    abstract protected static function getUciSection(&$configSection, $sectionName, $optionName, $content): void;

    /**
     * Execute the action in the uci system.
     * @param string $action
     * @param string $config
     * @param string $section
     * @param int $indexSection
     * @param string $option
     * @param string $value
     * @return array
     */
    abstract public function dispatchAction($action, $config, $section, $indexSection, $option, $value): array;

    /**
     * Return an array with services to restart.
     * @return array
     */
    public function getServices(): array
    {
        return $this->services;
    }
}
