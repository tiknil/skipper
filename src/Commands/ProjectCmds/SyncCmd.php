<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'sync', description: 'Sync your project by updating dependencies, doing migrations etc')]
class SyncCmd extends BaseCommand
{
    use WithProject;

    protected function handle(): int
    {
        Execute::onShell($this->project->composeCommand([
            'exec',
            $this->project->phpContainer,
            'composer',
            'install',
        ]));

        Execute::onShell($this->project->composeCommand([
            'exec',
            $this->project->phpContainer,
            'php',
            'artisan',
            'migrate',
        ]));

        Execute::hideOutput(['cd', $this->project->path], false);

        Execute::onShell(['yarn', 'install']);

        Execute::onShell(['yarn', 'build']);

        return Command::SUCCESS;
    }
}
