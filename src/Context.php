<?php

namespace UciGraphQL;

use UciGraphQL\Providers\UciProvider;

class Context
{
    /**
     * @var bool
     */
    public $isArraySection = false;
    /**
     * @var int
     */
    public $indexSection = UciProvider::IS_OBJECT_SECTION;
}
