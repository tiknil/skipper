<?php

namespace Tiknil\Skipper\Commands\ProxyCmd;

use Symfony\Component\Console\Attribute\AsCommand;
use Tiknil\Skipper\Commands\BaseCommand;

#[AsCommand(name: 'proxy:up', description: 'Launch the proxy docker compose instance')]
class UpCmd extends BaseCommand
{
    protected function handle(): int
    {
        $caddy = $this->configRepo->caddy;

        return $caddy->start();
    }
}
