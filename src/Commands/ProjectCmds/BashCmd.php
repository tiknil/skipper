<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\ShellCommand;

#[AsCommand(name: 'bash', description: 'Open a bash session inside the php-fpm container')]
class BashCmd extends BaseCommand
{
    use WithProject;

    protected function handle(): int
    {
        $this->checkRunning();

        $cmd = [
            ...$this->project->baseCommand(),
            'exec',
            $this->project->phpContainer,
            'bash',
        ];

        return ShellCommand::new()->useTty()
            ->run($cmd);
    }
}
