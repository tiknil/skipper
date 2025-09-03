<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\ShellCommand;

#[AsCommand(name: 'sync', description: 'Sync your project by updating dependencies, doing migrations etc')]
class SyncCmd extends BaseCommand
{
    use WithProject;

    protected function handle(): int
    {
        $this->io->writeln('<fg=bright-blue>Install composer dependencies</>');
        ShellCommand::new()->useTty()->run($this->project->composeCommand([
            'exec',
            $this->project->phpContainer,
            'composer',
            'install',
        ]));

        $this->io->writeln('-------------------');
        $this->io->writeln('<fg=bright-blue>Migrate database</>');

        ShellCommand::new()->useTty()->run($this->project->composeCommand([
            'exec',
            $this->project->phpContainer,
            'php',
            'artisan',
            'migrate',
        ]));

        ShellCommand::new()->showOutput(false)->showLog(false)->run(['cd', $this->project->path]);

        $this->io->writeln('-------------------');
        $this->io->writeln('<fg=bright-blue>Install js dependencies</>');

        ShellCommand::new()->useTty()->run(['yarn', 'install']);

        $this->io->writeln('-------------------');
        $this->io->writeln('<fg=bright-blue>Build frontend files</>');

        ShellCommand::new()->useTty()->run(['yarn', 'build']);

        return Command::SUCCESS;
    }
}
