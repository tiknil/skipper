<?php

namespace Tiknil\Skipper\Config;

use Tiknil\Skipper\Utils\Execute;

class Proxy
{
    public function __construct()
    {
    }

    public function check(): bool
    {
        return file_exists($this->proxyDir()) && file_exists($this->proxyDir().'/docker-compose.yml');
    }

    public function setup(): void
    {
        if (!file_exists($this->proxyDir())) {
            mkdir($this->proxyDir(), 0777, true);
        }

        Execute::hideOutput([
            'cp',
            '-r',
            path('proxy/'), // Keep the trailing /
            $this->proxyDir(),
        ]);
    }

    public function writeCaddyfile(Config $config): void
    {
        $fileContent = <<<'EOD'
{
    email local-certs@tiknil.com
    local_certs
}

EOD;

        foreach ($config->projects as $project) {
            $fileContent .= <<<EOD

{$project->host}:443 {
  encode gzip
  reverse_proxy {$project->name}-{$project->httpContainer}-1:80 {
       header_up X-Real-IP {remote_host}
  }
}

EOD;
        }

        file_put_contents($this->caddyfilePath(), $fileContent);
    }

    public function start(): int
    {
        $cmd = [
            ...$this->baseCommand(),
            'up',
            '-d',
            '--remove-orphans',
        ];

        return Execute::onShell($cmd);
    }

    public function reload(): int
    {
        $cmd = [
            ...$this->baseCommand(),
            'restart',
            'caddy',
        ];

        return Execute::onShell($cmd);
    }

    public function stop(): int
    {
        $cmd = [
            ...$this->baseCommand(),
            'down',
        ];

        return Execute::onShell($cmd);
    }

    public function certPath(): string
    {
        return $this->proxyDir().'/data/caddy/pki/authorities/local/root.crt';
    }

    public function caddyfilePath(): string
    {
        return $this->proxyDir().'/Caddyfile';
    }

    private function baseCommand(): array
    {
        return [
            'docker',
            'compose',
            '-f',
            $this->proxyDir().'/docker-compose.yml',
        ];
    }

    private function proxyDir(): string
    {
        return rtrim(getenv('HOME'), '/').'/.skipper/proxy';
    }
}
