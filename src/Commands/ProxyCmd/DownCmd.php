<?php

namespace Tiknil\Skipper\Commands\ProxyCmd;

use Symfony\Component\Console\Attribute\AsCommand;
use Tiknil\Skipper\Commands\BaseCommand;

#[AsCommand(name: 'proxy:down', description: 'Stop the proxy docker compose instance')]
class DownCmd extends BaseCommand
{
    protected function handle(): int
    {

        $this->io->info(['If you have at least one project running, docker will not be able to dismiss the skipper network']);
        $caddy = $this->configRepo->caddy;

        return $caddy->stop();
    }
}
