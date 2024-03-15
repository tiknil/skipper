<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'mysql', description: 'Start a mysql shell')]
class MysqlCmd extends BaseCommand
{
    use WithProject;

    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'user',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'MySQL user',
                        'root'
                    ),
                    new InputOption(
                        'psw',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'MySQL user password',
                        'root'
                    ),
                    new InputOption(
                        'container',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'MySQL container name',
                        'mysql'
                    ),
                ])
            );
    }

    protected function handle(): int
    {
        $this->checkRunning();

        $dbUser = $this->input->getOption('user');
        $dbPsw = $this->input->getOption('psw');

        $container = $this->input->getOption('container');

        $cmd = [
            ...$this->project->baseCommand(),
            'exec',
            $container,
            'mysql',
            '-u',
            $dbUser,
            '-p'.$dbPsw,
        ];

        return Execute::onTty($cmd);

    }
}
