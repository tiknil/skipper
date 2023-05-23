<?php

namespace Tiknil\Skipper\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tiknil\Skipper\Config\Project;
use Tiknil\Skipper\Utils\DockerBase;
use Tiknil\Skipper\Utils\HostFile;

#[AsCommand(name: 'init', description: 'Create a new Skipper project in the current directory')]
class InitCmd extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'host',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'The project host'
                    ),
                    new InputOption(
                        'name',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'The project name. Must be unique'
                    ),
                    new InputOption(
                        'compose-file',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'Path to the docker compose file',
                        'docker/docker-compose.yml'
                    ),
                    new InputOption(
                        'env-file',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'Path to an optional .env file for docker compose',
                        'docker/.env'
                    ),
                    new InputOption(
                        'http-container',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'The container handling http requests',
                        'nginx'
                    ),
                    new InputOption(
                        'php-container',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'The php container',
                        'php-fpm'
                    ),
                    new InputOption(
                        'docker-base',
                        '',
                        InputOption::VALUE_NONE,
                        'Force a new install of the base docker compose directory at this path'
                    ),
                ])
            );
    }

    protected function handle(): int
    {
        $project = $this->configRepo->config->projectByPath(getcwd(), false);

        if (!empty($project)) {
            $this->io->warning([
                'Invalid path',
                'The current path is already in use for project '.$project->name,
            ]);

            return Command::FAILURE;

        }

        $path = rtrim(getcwd(), '/');

        $this->preparePath($path);

        $name = $this->validateName();
        if (empty($name)) {
            return Command::FAILURE;
        }

        $host = $this->validateHost($name);
        if (empty($host)) {
            return Command::FAILURE;
        }

        $composeFile = $this->validateComposeFile();
        if (empty($composeFile)) {
            return Command::SUCCESS;
        }

        $envFile = $this->input->getOption('env-file');
        $httpContainer = $this->input->getOption('http-container');
        $phpContainer = $this->input->getOption('php-container');

        $project = new Project(
            path: $path,
            name: $name,
            host: $host,
            composeFile: $composeFile,
            envFile: $envFile,
            httpContainer: $httpContainer,
            phpContainer: $phpContainer
        );

        $this->io->definitionList(
            ...$project->definitionList()
        );

        $this->io->writeln('All values can be configured through command line options. See <info>skipper init help</info> for more details');

        $confirm = $this->io->confirm('Proceed with the project creation?', true);

        if (!$confirm) {
            return Command::SUCCESS;
        }

        $this->configRepo->config->projects[$project->name] = $project;
        $this->configRepo->updateConfig();

        $this->configRepo->caddy->writeCaddyfile($this->configRepo->config);
        $this->configRepo->caddy->reload();

        HostFile::for($project->host)->requestAdd();

        $this->io->success("$name has been created successfully.");

        $this->io->writeln('⛵️ Use command <info>skipper sail</info> to start the project containers');
        $this->io->writeln('✓ Use command <info>skipper check</info> to validate your project compose file');

        return Command::SUCCESS;
    }

    private function preparePath(): void
    {
        // Check if this is a Laravel project

        if (!file_exists('composer.json')) {
            $this->io->warning([
                'This is not a PHP project',
                'Skipper is developed for Laravel projects and will probably not work with different stacks',
            ]);

            if (!$this->io->confirm('Proceed anyway?', false)) {
                exit(0);
            }
        } else {
            $compJson = json_decode(file_get_contents('composer.json'), true);

            if (!isset($compJson['require']['laravel/framework'])) {
                $this->io->warning([
                    'This is not a Laravel project',
                    'Skipper is developed for Laravel projects and will probably not work with different stacks',
                ]);

                if (!$this->io->confirm('Proceed anyway?', false)) {
                    exit(0);
                }
            }
        }

        $dockerBase = $this->input->getOption('docker-base');
        $composeFile = $this->input->getOption('compose-file');

        if (!$dockerBase && !file_exists($composeFile)) {
            $this->io->warning([
                'Docker compose not found',
                "Skipper can prepare your project with a default configuration from\n{$this->configRepo->config->dockerBaseUrl}",
            ]);

            $dockerBase = $this->io->confirm('Add default configuration to this project?');
        }

        if ($dockerBase) {
            DockerBase::install(getcwd(), 'docker');

            $this->io->writeln('<info>Resuming the skipper init operation</info>');
            $this->io->newLine();
        }
    }

    private function validateName(): string|null
    {
        $name = $this->input->getOption('name');
        if (empty($name)) {
            $dirName = slug(basename(getcwd()));
            $name = $this->io->ask('Project name', $dirName);
        }

        $projectByName = $this->configRepo->config->projectByName($name);

        if (!empty($projectByName)) {
            $this->io->error([
                'Invalid name',
                $name.' is already in use for project at path '.$projectByName->path,
            ]);

            return null;
        }

        return $name;
    }

    private function validateHost(string $name): string|null
    {
        $host = $this->input->getOption('host');
        if (empty($host)) {
            $host = $this->io->ask('Project Host (do not include http/https)', "$name.localhost");
        }

        $host = rtrim($host, '/');

        if (str_starts_with($host, 'http')) {
            $this->io->error([
                'Host should not contain protocol http',
            ]);

            return null;
        }

        $projectByHost = $this->configRepo->config->projectByHost($host);

        if (!empty($projectByHost)) {
            $this->io->error([
                'Invalid host',
                'The current host is already in use for project '.$projectByHost->name,
            ]);

            return null;
        }

        return $host;
    }

    private function validateComposeFile(): string|null
    {
        $composeFile = $this->input->getOption('compose-file');

        if (!file_exists($composeFile)) {
            $composeFile = $this->io->ask('Relative path to the docker-compose file', 'docker/docker-compose.yml');
        }

        if (!file_exists($composeFile)) {
            $confirm = $this->io->confirm("File '$composeFile' not found. Proceed anyway?");

            if (!$confirm) {
                return null;
            }
        }

        return $composeFile;
    }
}
