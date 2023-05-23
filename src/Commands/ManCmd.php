<?php

namespace Tiknil\Skipper\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'man', description: 'Explains how to use this software')]
class ManCmd extends BaseCommand
{
    protected bool $withConfig = false;

    protected function handle(): int
    {
        $app = $this->getApplication();

        $all = $app->all();

        $this->io->writeln("{$app->getName()} <info>{$app->getVersion()}</info>");
        $this->io->newLine();

        $this->io->writeln('<comment>Usage</comment>');

        $this->io->text('skipper [command] [options] [arguments]');

        $this->io->newLine();

        $this->io->writeln('<comment>Default options</comment>');
        $this->io->writeln('Available for all commands');
        $this->io->definitionList(
            ['-h, --help' => 'Display help for the given command'],
            ['-q, --quiet' => 'Do not output any message'],
            ['-V, --version' => 'Display the current skipper version'],
            ['-n, --no-interaction' => 'Do not ask any interactive question'],
        );

        $this->io->writeln('<info>COMMANDS</info>');

        $this->io->writeln(' <comment>Proxy</comment>');
        $this->io->definitionList(
            ['proxy:certs' => ' Install proxy local root certificate'],
            ['proxy:up' => 'Launch the proxy docker compose instance'],
            ['proxy:down' => 'Stop the proxy docker compose instance'],
            ['proxy:restart' => 'Restart the proxy container instance'],
            ['proxy:show' => 'Print the current Caddyfile configuration'],
            ['proxy:config' => 'Regenerate the proxy configuration file'],
            ['proxy:update' => ' Update the proxy deployment after a skipper upgrade'],
        );

        $this->io->writeln(' <comment>Helpers</comment>');
        $this->io->definitionList(
            ['list' => 'List all skipper registered projects'],
            ['shutdown' => 'Stop all skipper projects and the reverse proxy'],
            ['host' => 'Register a new host in your /etc/hosts file'],
        );

        $this->io->writeln(' <comment>Project management</comment>');
        $this->io->definitionList(
            ['init' => 'Create a new Skipper project'],
            ['sail' => 'Start a skipper project'],
            ['dock' => 'Stop a skipper project'],
            ['edit' => 'Edit a skipper project fields'],
            ['rm' => 'Removes a skipper project reference'],
            ['info' => 'Display the skipper project summary'],
            ['check' => 'Check if the docker-compose file has some issues'],
            ['compose' => 'Run a docker-compose command for the project'],
            ['docker-base' => 'Reinstall the default docker base setup'],
        );

        $this->io->writeln(' <comment>Project utils</comment>');
        $this->io->definitionList(
            ['bash' => 'Start a bash session in the php container'],
            ['composer' => 'Run a composer command'],
            ['artisan' => 'Run an artisan command (alias: art)'],
            ['tinker' => 'Start a new Tinker shell (alias: tk)'],
            ['sync' => 'Update dependencies, do migrations and sync your project'],
            ['backup' => 'Create a new MySQL backup'],
            ['restore' => 'Restore a MySQL backup'],
        );

        $this->io->writeln('Refer to https://github.com/tiknil/skipper for additional documentation');
        $this->io->writeln('Use <info>skipper help [command]</info> or <info>skipper [command] --help</info> for details about a specific command');

        return Command::SUCCESS;
    }
}
