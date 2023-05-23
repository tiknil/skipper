<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
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

        Execute::logCmd([...$dumpCmd, '|', 'gzip', '>', $outputFile]);

        if (!file_exists(dirname($outputFile))) {
            mkdir(dirname($outputFile), 0777, true);
        }

        $fileStream = fopen($outputFile, 'w');

        // Se il file va compressato, serve una fase intermedi
        $sqlOutput = new InputStream();

        $gzipProcess = new Process(['gzip']);
        $gzipProcess->setInput($sqlOutput);

        $dumpProcess = new Process($dumpCmd);
        $dumpProcess->start(function ($type, $buffer) use ($sqlOutput) {
            if (Process::ERR === $type) {
                $this->output->write($buffer);
            } else {
                $sqlOutput->write($buffer);
            }
        });

        $gzipProcess->start(function ($type, $buffer) use ($fileStream) {
            if (Process::ERR === $type) {
                $this->output->write($buffer);
            } else {
                fwrite($fileStream, $buffer);
            }
        });

        $dumpResult = $dumpProcess->wait();

        $sqlOutput->close();
        $gzipResult = $gzipProcess->wait();

        $dumpResult = $gzipResult === Command::SUCCESS ? $dumpResult : Command::FAILURE;

        fclose($fileStream);

        if ($dumpResult === Command::SUCCESS) {
            $this->io->success("Backup $outputFile created successfully");
        }

        $uncompressedFile = rtrim($outputFile, '.gz');

        $this->io->writeln(['You can uncompress it using']);
        $this->io->writeln("<fg=green>gunzip < $outputFile > $uncompressedFile</>");

        return $dumpResult;

    }

    private function defaultFile(): string
    {
        $date = date('ymd_His');

        return "docker/backups/{$this->project->name}_$date.sql.gz";
    }
}
