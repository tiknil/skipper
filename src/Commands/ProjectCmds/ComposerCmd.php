<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'composer', aliases: ['comp'], description: 'Run a composer command')]
class ComposerCmd extends BaseCommand
{
    use WithProject;

    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this->addArgument('cmd', InputArgument::IS_ARRAY);

        $this->addUsage('install');
        $this->addUsage('dump-autoload');
        $this->addUsage('require tiknil/wire-table');
    }

    protected function handle(): int
    {
        $this->checkRunning();

        $argv = $_SERVER['argv'];
        array_shift($argv);
        array_shift($argv);

        return Execute::onTty([
            ...$this->project->baseCommand(),
            'exec',
            $this->project->phpContainer,
            'composer',
            ...$argv,
        ]);

    }
}
