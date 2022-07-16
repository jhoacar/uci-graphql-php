<?php

declare(strict_types=1);

namespace UciGraphQL\Utils;

/**
 * Class used for execute commands in shell.
 */
class Command
{
    /**
     * Return a string with the stdoutput and stderr for the command executed.
     * @param string $command
     * @return string
     */
    public static function execute($command): string
    {
        /* Using 2>&1 we redirect stderr to stdout */
        return shell_exec("$command 2>&1") ?: '';
    }
}
