<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\ShellCommand;

#[AsCommand(name: 'restore', description: 'Restore a MySQL backup')]
class RestoreCmd extends BaseCommand
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
                        'dbuser'
                    ),
                    new InputOption(
                        'psw',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'MySQL user password',
                        'dbpsw'
                    ),
                    new InputOption(
                        'db',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'MySQL database name',
                        'dbname'
                    ),
                    new InputOption(
                        'container',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'MySQL container name',
                        'mysql'
                    ),
                    new InputOption(
                        'file',
                        'f',
                        InputOption::VALUE_REQUIRED,
                        'Path to the input file (REQUIRED)',
                    ),
                ])
            );
    }

    protected function handle(): int
    {
        $this->checkRunning();

        $dbUser = $this->input->getOption('user');
        $dbPsw = $this->input->getOption('psw');
        $dbName = $this->input->getOption('db');

        $container = $this->input->getOption('container');

        $file = $this->input->getOption('file');
        if (empty($file)) {
            $this->io->warning('Input file is missing, use --file [filepath]');

            return Command::SUCCESS;
        }

        if (!file_exists($file)) {
            $this->io->warning("Input file $file does not exists");

            return Command::SUCCESS;
        }

        $mysqlCmd = [
            ...$this->project->baseCommand(),
            'exec',
            '-e',
            "MYSQL_PWD=$dbPsw",
            '-T',
            $container,
            'mysql',
            '-u',
            $dbUser,
            $dbName,
        ];

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        $this->io->writeln('<comment>⚠️  Restoring the DB is a risky operation. You may lose your data ⚠️</comment>');
        $this->io->newLine();
        $this->io->writeln("Input file: <comment>$file</comment>");

        $confirm = $this->io->confirm('Proceed anyway?');

        if (!$confirm) {
            return Command::SUCCESS;
        }

        $fullCmd = ['gunzip', '<', $file, '|', ...$mysqlCmd];

        $result = ShellCommand::new()->useShellIntegration()->run($fullCmd);

        if ($result === Command::SUCCESS) {
            $this->io->success("✅ Backup $file restored successfully");
        } else {
            $this->io->error('An error occurred');

            $this->io->text('Try running directly the full command you find above, it may work in case of an internal skipper php problem');
        }

        return $result;

    }
}
