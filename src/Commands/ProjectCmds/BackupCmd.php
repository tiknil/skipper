<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'backup', description: 'Create a new MySQL backup')]
class BackupCmd extends BaseCommand
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
                        'output',
                        'o',
                        InputOption::VALUE_REQUIRED,
                        'Path to the output file',
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

        $outputFile = $this->input->getOption('output');
        if (empty($outputFile)) {
            $outputFile = $this->defaultFile();
        }

        $dumpCmd = [
            ...$this->project->baseCommand(),
            'exec',
            '-e',
            "MYSQL_PWD=$dbPsw",
            $container,
            'mysqldump',
            '-u',
            $dbUser,
            $dbName,
            '--no-tablespaces',
        ];

        if (!file_exists(dirname($outputFile))) {
            mkdir(dirname($outputFile), 0777, true);
        }

        $fullCmd = [...$dumpCmd, '|', 'gzip', '>', $outputFile];

        $result = Execute::onShellCli($fullCmd);

        if ($result === Command::SUCCESS) {
            $this->io->writeln('✅ <info>Backup created successfully</info>');
            $this->io->writeln("📦 $outputFile");
        }

        $uncompressedFile = rtrim($outputFile, '.gz');

        $this->io->newLine();
        $this->io->writeln(['You can uncompress it using']);
        $this->io->writeln("<comment>gunzip < $outputFile > $uncompressedFile</comment>");

        $this->io->newLine();
        $this->io->writeln("Use <info>skipper restore --file $outputFile</info> to restore");

        return $result;

    }

    private function defaultFile(): string
    {
        $date = date('ymd_His');

        return "docker/backups/{$this->project->name}_$date.sql.gz";
    }
}
