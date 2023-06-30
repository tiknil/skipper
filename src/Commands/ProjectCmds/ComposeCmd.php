<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'compose', description: 'Forward the arguments to the base docker compose command')]
class ComposeCmd extends BaseCommand
{
    use WithProject;

    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this->addArgument('cmd', InputArgument::IS_ARRAY, 'The command to run as docker compose');

        $this->addUsage('exec mysql mysql -u root -p');
        $this->addUsage('logs nginx');
    }

    protected function handle(): int
    {
        $argv = $_SERVER['argv'];
        array_shift($argv);
        array_shift($argv);

        return Execute::onTty([...$this->project->baseCommand(), ...$argv]);

    }
}
