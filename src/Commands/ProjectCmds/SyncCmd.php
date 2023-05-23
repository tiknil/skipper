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
        $this->io->writeln('<fg=bright-blue>Install composer dependencies</>');
        Execute::onShell($this->project->composeCommand([
            'exec',
            $this->project->phpContainer,
            'composer',
            'install',
        ]));

        $this->io->writeln('-------------------');
        $this->io->writeln('<fg=bright-blue>Migrate database</>');

        Execute::onShell($this->project->composeCommand([
            'exec',
            $this->project->phpContainer,
            'php',
            'artisan',
            'migrate',
        ]));

        Execute::hideOutput(['cd', $this->project->path], false);

        $this->io->writeln('-------------------');
        $this->io->writeln('<fg=bright-blue>Install js dependencies</>');

        Execute::onShell(['yarn', 'install']);

        $this->io->writeln('-------------------');
        $this->io->writeln('<fg=bright-blue>Build frontend files</>');

        Execute::onShell(['yarn', 'build']);

        return Command::SUCCESS;
    }
}
