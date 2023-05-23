<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;
use Tiknil\Skipper\Utils\HostFile;

#[AsCommand(name: 'sail', description: 'Start the project')]
class SailCmd extends BaseCommand
{
    use WithProject;

    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'build',
                        'b',
                        InputOption::VALUE_NONE,
                        'Force a new containers build'
                    ),
                ])
            );
    }

    protected function handle(): int
    {
        $this->io->definitionList(
            ...$this->project->definitionList()
        );

        $result = $this->checkComposeFile();

        if (!$result) {
            $confirm = $this->io->confirm('We found some problems on your compose file. Proceed anyway?');

            if (!$confirm) {
                return Command::SUCCESS;
            }
        }

        $shouldBuild = $this->input->getOption('build');

        // Start caddy
        $this->configRepo->caddy->start();

        // Start project
        $cmd = [...$this->project->baseCommand(), 'up', '-d', '--remove-orphans'];

        if ($shouldBuild) {
            $cmd[] = '--build';
        }

        $result = Execute::onShell($cmd);

        if ($result === Command::FAILURE) {
            return Command::FAILURE;
        }

        if (!HostFile::for($this->project->host)->check()) {
            $this->io->writeln([
                "Host {$this->project->host} is not registered inside your /etc/hosts file.",
                "Use command <info>skipper host {$this->project->host}</info>",
            ]);

            $this->io->newLine();
        }

        $this->io->writeln([
            "<comment>{$this->project->name}</comment> is up and running at <info>https://{$this->project->host}</info>",
        ]);

        return Command::SUCCESS;

    }
}
