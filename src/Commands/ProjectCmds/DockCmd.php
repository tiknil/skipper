<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'dock', description: 'Stop the project containers', aliases: ['stop', 'down'])]
class DockCmd extends BaseCommand
{
    use WithProject;

    protected function handle(): int
    {
        $cmd = [
            ...$this->project->baseCommand(),
            'down',
        ];

        $result = Execute::onShell($cmd);

        if ($result === Command::SUCCESS) {
            $this->io->writeln('Use command <info>caddy stop</info> to stop the reverse proxy');
            $this->io->writeln('Use command <info>shutdown</info> to stop all running projects and the reverse proxy');
        }

        return $result;
    }
}
