<?php

namespace Tiknil\Skipper\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tiknil\Skipper\Utils\HostFile;

#[AsCommand(name: 'host', description: 'Add or remove host from /etc/hosts', hidden: true)]
class HostCmd extends BaseCommand
{
    protected function configure()
    {
        $this->setDefinition(
            new InputDefinition([
                new InputArgument(
                    'host',
                    InputArgument::REQUIRED,
                    'The host to add or remove'
                ),
                new InputOption(
                    'remove',
                    'r',
                    InputOption::VALUE_NONE,
                    'If set, the host will be removed from the file',
                ),
                new InputOption(
                    'ip',
                    '',
                    InputOption::VALUE_REQUIRED,
                    'The IP that the host should map to',
                    '127.0.0.1'
                ),
            ])
        );
    }

    protected function handle(): int
    {
        $host = $this->input->getArgument('host');
        $remove = $this->input->getOption('remove');

        $ip = $this->input->getOption('ip');

        $hostFile = HostFile::for($host, $ip);

        $remove
            ? $hostFile->requestRemove()
            : $hostFile->requestAdd();

        return Command::SUCCESS;
    }
}
