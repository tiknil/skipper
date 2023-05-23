<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;

#[AsCommand(name: 'info', description: 'Displays the project info')]
class InfoCmd extends BaseCommand
{
    use WithProject;

    protected function handle(): int
    {
        $this->io->definitionList(
            ...$this->project->definitionList()
        );

        return Command::SUCCESS;

    }
}
