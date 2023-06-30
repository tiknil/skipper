<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'tinker', description: 'Open a tinker session inside the php-fpm container', aliases: ['tk'])]
class TinkerCmd extends BaseCommand
{
    use WithProject;

    protected function handle(): int
    {
        $this->checkRunning();

        $cmd = [
            ...$this->project->baseCommand(),
            'exec',
            $this->project->phpContainer,
            'php',
            'artisan',
            'tinker',
        ];

        return Execute::onTty($cmd);

    }
}
