<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\DockerBase;

#[AsCommand(name: 'docker-base', description: 'Reinstall the default docker base setup')]
class DockerBaseCmd extends BaseCommand
{
    use WithProject;

    protected function handle(): int
    {
        DockerBase::install($this->project->path);

        return Command::SUCCESS;

    }
}
