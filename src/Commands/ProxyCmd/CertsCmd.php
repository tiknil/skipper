<?php

namespace Tiknil\Skipper\Commands\ProxyCmd;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'proxy:certs', description: 'Install proxy local root certificate')]
class CertsCmd extends BaseCommand
{
    protected function handle(): int
    {
        $caddy = $this->configRepo->caddy;

        $this->io->writeln('📝 The proxy (Caddy) creates a local root certificate to sign its https certificates');
        $this->io->writeln("The OS normally doesn't trust this root certificate, but we can install it on our system");

        $this->io->newLine();

        $uid = posix_getuid();

        $this->io->writeln('🧐 Looking for root certificate in <info>'.$caddy->certPath().'</info>');

        if (!file_exists($caddy->certPath())) {
            $this->io->note([
                'Root cert not found.',
                'Caddy generates the certs the first time it starts with a project enabled',
                'Try sailing a project before running this command',
            ]);

            return Command::SUCCESS;
        }

        $this->io->newLine();

        $cmd = [];

        $this->io->writeln('🔐 <info>Mac OS X will prompt for your Touch ID or password</info>');

        if ($uid !== 0) {
            $cmd[] = 'sudo';
        }

        $cmd[] = 'security';
        $cmd[] = 'add-trusted-cert';
        $cmd[] = '-d';
        $cmd[] = '-k';
        $cmd[] = '/Library/Keychains/System.keychain';
        $cmd[] = $caddy->certPath();

        $result = Execute::onTty($cmd);

        if ($result === Command::SUCCESS) {
            $this->io->success('✅ Cert installed correctly');

            $this->io->writeln([
                '⏳ The browser may need some time to refresh its cache and recognize the certificate as valid',
            ]);
        }

        return $result;
    }
}
