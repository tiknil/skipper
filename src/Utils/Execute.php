<?php

namespace Tiknil\Skipper\Utils;

use Symfony\Component\Process\Process;

class Execute
{
    public static function onShell(array $cmd, bool $log = true): int
    {
        if ($log) {
            self::logCmd($cmd);
        }

        $process = new Process($cmd);
        $process->setTimeout(0);
        $process->setTty(true);

        $result = $process->run();

        Globals::$output->writeln('');

        return $result;
    }

    public static function onOutput(array $cmd, bool $log = true): int
    {
        if ($log) {
            self::logCmd($cmd);
        }

        $process = new Process($cmd);

        $process->enableOutput();

        $result = $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        echo "\n";

        return $result;
    }

    public static function hideOutput(array $cmd, bool $log = true): int
    {
        if ($log) {
            self::logCmd($cmd);
        }

        $process = new Process($cmd);
        $process->disableOutput();

        return $process->run();
    }

    public static function logCmd(array $cmd): void
    {
        Globals::$output->writeln('<fg=gray> >>> '.implode(' ', $cmd)."</>\n");
    }
}
