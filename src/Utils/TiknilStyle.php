<?php

namespace Tiknil\Skipper\Utils;

use Symfony\Component\Console\Style\SymfonyStyle;

class TiknilStyle extends SymfonyStyle
{
    public function info(string|array $message)
    {
        $this->block($message, 'INFO', 'fg=green', ' ', false);
    }

    public function infoText(string|array $message)
    {
        $messages = is_string($message) ? [$message] : $message;

        foreach ($messages as $msg) {

            $this->writeln("<info> [INFO] $msg</info>");

        }
    }
}
