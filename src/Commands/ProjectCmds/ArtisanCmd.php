<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'artisan', aliases: ['art'], description: 'Run an artisan command')]
class ArtisanCmd extends BaseCommand
{
    use WithProject;

    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this->addArgument('cmd', InputArgument::IS_ARRAY);

        $this->addUsage('migrate');
        $this->addUsage('ide-helper:models --write');
        $this->addUsage('make:migration alter_users_table');
    }

    protected function handle(): int
    {
        $this->checkRunning();

        $argv = $_SERVER['argv'];
        array_shift($argv);
        array_shift($argv);

        return Execute::onShell([
            ...$this->project->baseCommand(),
            'exec',
            $this->project->phpContainer,
            'php',
            'artisan',
            ...$argv,
        ]);

    }
}
