<?php

declare(strict_types=1);

namespace UciGraphQL\Utils;

/**
 * Class used for execute commands in shell.
 */
class Command
{
    const NO_ERRORS = 0;

    /**
     * Return a string with the stdoutput and stderr for the command executed.
     * @param string $command
     * @param int &$result_code [optional]
     * If the return_var argument is present along with the output argument, then the return status of the executed command will be written to this variable.
     * @return string
     */
    public static function execute($command, &$result_code = self::NO_ERRORS): string
    {
        $output = [];
        exec($command, $output, $result_code);
        $result = '';
        foreach ($output as $line) {
            $result .= $line . PHP_EOL;
        }

        return $result;
    }
}
