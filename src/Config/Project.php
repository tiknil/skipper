<?php

namespace Tiknil\Skipper\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class Project
{
    public function __construct(
        public string $path,
        public string $name,
        public string $host,
        public string $composeFile = 'docker/docker-compose.yml',
        public string $envFile = 'docker/.env',
        public string $httpContainer = 'nginx',
        public string $phpContainer = 'php-fpm',
    ) {
    }

    public static function from(array $data): self
    {
        return new self(
            path: $data['path'],
            name: $data['name'],
            host: $data['host'],
            composeFile: $data['composeFile'] ?? 'docker/docker-compose.yml',
            envFile: $data['envFile'] ?? 'docker/.env',
            httpContainer: $data['httpContainer'] ?? 'nginx',
            phpContainer: $data['phpContainer'] ?? 'php-fpm'
        );
    }

    public function baseCommand(): array
    {
        $cmd = [
            'docker',
            'compose',
            '--project-name',
            $this->name,
            '-f',
            $this->composeFilePath(),
        ];

        if (!empty($this->envFile) && file_exists($this->envFilePath())) {
            $cmd[] = '--env-file';
            $cmd[] = $this->envFilePath();
        }

        return $cmd;
    }

    public function composeCommand(array|string $cmd): array
    {
        $cmd = is_string($cmd) ? explode(' ', $cmd) : $cmd;

        return [
            ...$this->baseCommand(),
            ...$cmd,
        ];
    }

    public function composeFilePath(): string
    {
        if (str_starts_with($this->composeFile, '/')) {
            return $this->composeFile;
        }

        if (rtrim(getcwd(), '/') === rtrim($this->path, '/')) {
            return $this->composeFile;
        }

        return rtrim($this->path, '/').'/'.$this->composeFile;

    }

    public function envFilePath(): string
    {
        if (empty($this->envFile)) {
            return '';
        }

        if (str_starts_with($this->envFile, '/')) {
            return $this->envFile;
        }

        if (rtrim(getcwd(), '/') === rtrim($this->envFile, '/')) {
            return $this->envFile;
        }

        return rtrim($this->path, '/').'/'.$this->envFile;

    }

    public function definitionList(): array
    {
        return [
            ['name' => $this->name],
            ['host' => $this->host],
            ['path' => $this->path],
            ['composeFile' => $this->composeFile],
            ['envFile' => $this->envFile ?: ''],
            ['httpContainer' => $this->httpContainer],
            ['phpContainer' => $this->phpContainer],
        ];
    }

    public function isRunning(): bool
    {
        $process = new Process([
            ...$this->baseCommand(),
            'ps',
            $this->phpContainer,
            '--format=json',
        ]);

        $result = $process->run();

        return $result === Command::SUCCESS && trim($process->getOutput()) !== '[]';
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'name' => $this->name,
            'host' => $this->host,
            'composeFile' => $this->composeFile,
            'envFile' => $this->envFile,
            'httpContainer' => $this->httpContainer,
            'phpContainer' => $this->phpContainer,
        ];
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }
}
