<?php

namespace Tiknil\Skipper\Commands\ProxyCmd;

use Symfony\Component\Console\Attribute\AsCommand;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Utils\ShellCommand;

#[AsCommand(name: 'proxy:show', description: 'Print the current Caddyfile configuration')]
class ShowCmd extends BaseCommand
{
    protected function handle(): int
    {
        $caddy = $this->configRepo->caddy;

        return ShellCommand::new()->useTty()->run(['less', $caddy->caddyfilePath()]);
    }
}
