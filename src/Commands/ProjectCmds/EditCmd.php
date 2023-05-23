<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Config\Project;
use Tiknil\Skipper\Utils\HostFile;

#[AsCommand(name: 'edit', description: 'Edit project configuration')]
class EditCmd extends BaseCommand
{
    use WithProject;

    protected function configure(): void
    {
        $this->recursive = false;
        parent::configure();
    }

    protected function handle(): int
    {
        $name = $this->validateName();
        if (empty($name)) {
            return Command::SUCCESS;
        }

        $host = $this->validateHost();
        if (empty($host)) {
            return Command::SUCCESS;
        }

        $composeFile = $this->validateComposeFile();
        if (empty($composeFile)) {
            return Command::SUCCESS;
        }

        $envFile = $this->validateEnvFile();
        $httpContainer = $this->validateHttpContainer();
        $phpContainer = $this->validatePhpContainer();

        $updatedProj = new Project(
            path: $this->project->path,
            name: $name,
            host: $host,
            composeFile: $composeFile,
            envFile: $envFile,
            httpContainer: $httpContainer,
            phpContainer: $phpContainer
        );

        $this->io->definitionList(
            ...$updatedProj->definitionList()
        );

        $confirm = $this->io->confirm('Save this updated data?', true);

        if (!$confirm) {
            return Command::SUCCESS;
        }

        if ($name !== $this->project->name) {
            unset($this->configRepo->config->projects[$this->project->name]);
        }

        $this->configRepo->config->projects[$updatedProj->name] = $updatedProj;
        $this->configRepo->updateConfig();

        $this->configRepo->caddy->writeCaddyfile($this->configRepo->config);
        $this->configRepo->caddy->reload();

        $this->io->success('Project updated successfully');

        if ($name !== $this->project->name) {
            $this->io->writeln('You need to run the <info>sail</info> command to apply your new name');
        }

        if ($updatedProj->host !== $this->project->host) {
            if ($this->io->confirm('Allow skipper to update your /etc/hosts file?')) {
                HostFile::for($this->project->host)->requestRemove();
                HostFile::for($updatedProj->host)->requestAdd();
            }
        }

        return Command::SUCCESS;
    }

    private function validateName(): string|null
    {
        $name = $this->io->ask('Project name', $this->project->name);

        if ($name !== $this->project->name) {
            if ($this->configRepo->config->projectByName($name) !== null) {

                $this->io->error([
                    'Invalid name',
                    "Project $name already exists",
                ]);

                return null;
            }

            $this->io->warning([
                'Potential data loss',
                "Changing the project name to $name will replace existing docker volumes.\n".
                'The data in your local volumes (e.g. MySQL) will be lost',
            ]);

            $confirm = $this->io->confirm('Proceed anyway?');

            if (!$confirm) {
                return null;
            }
        }

        return $name;
    }

    private function validateHost(): string|null
    {
        $host = $this->io->ask('Project Host (do not include http/https)', $this->project->host);

        if (str_starts_with($host, 'http')) {
            $this->io->error([
                'Host should not contain protocol http',
            ]);

            return null;
        }

        if ($host !== $this->project->host && $this->configRepo->config->projectByHost($host) !== null) {

            $this->io->error([
                'Invalid host',
                "$host is already in use",
            ]);

            return null;
        }

        return $host;
    }

    private function validateComposeFile(): string|null
    {
        $composeFile = $this->io->ask('Relative path to the docker-compose file', $this->project->composeFile);

        if (!file_exists($composeFile)) {
            $confirm = $this->io->confirm("File '$composeFile' not found. Proceed anyway?");

            if (!$confirm) {
                return null;
            }
        }

        return $composeFile;
    }

    private function validateEnvFile(): string
    {
        return $this->io->ask('Docker compose env file (optional)', $this->project->envFile);
    }

    private function validateHttpContainer(): string
    {
        return $this->io->ask('HTTP container name', $this->project->httpContainer);
    }

    private function validatePhpContainer(): string
    {
        return $this->io->ask('PHP container name', $this->project->phpContainer);
    }
}
