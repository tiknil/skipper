<?php

namespace Tiknil\Skipper\Commands\ProxyCmd;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;

#[AsCommand(name: 'proxy:update', description: 'Update the proxy deployment after a skipper upgrade')]
class UpdateCmd extends BaseCommand
{
    protected function handle(): int
    {
        $caddy = $this->configRepo->caddy;

        $caddy->setup();

        $caddy->start();
        $caddy->reload();

        return Command::SUCCESS;
    }
}
