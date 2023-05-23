<?php

namespace Tiknil\Skipper\Commands\ProxyCmd;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;

#[AsCommand(name: 'proxy:config', description: 'Regenerate the proxy configuration file')]
class ConfigCmd extends BaseCommand
{
    protected function handle(): int
    {
        $caddy = $this->configRepo->caddy;

        $caddy->writeCaddyfile($this->configRepo->config);

        $this->io->success('Configuration updated');

        $this->io->writeln('Use <info>skipper proxy:show</info> to see the updated file');
        $this->io->writeln('Use <info>skipper proxy:restart</info> to see the new configuration in action');

        return Command::SUCCESS;
    }
}
