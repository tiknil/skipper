<?php

namespace Tiknil\Skipper\Commands\ProxyCmd;

use Symfony\Component\Console\Attribute\AsCommand;
use Tiknil\Skipper\Commands\BaseCommand;

#[AsCommand(name: 'proxy:restart', description: 'Restart the proxy instance')]
class RestartCmd extends BaseCommand
{
    protected function handle(): int
    {
        $caddy = $this->configRepo->caddy;

        return $caddy->reload();
    }
}
