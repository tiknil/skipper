<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;

#[AsCommand(name: 'check', description: 'Check if the docker-compose file has some issues')]
class CheckCmd extends BaseCommand
{
    use WithProject;

    protected function handle(): int
    {
        if ($this->checkComposeFile()) {
            $this->io->success('No issues found');
        }

        return Command::SUCCESS;

    }
}
