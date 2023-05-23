<?php

namespace Tiknil\Skipper\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'shutdown', description: 'Stops all skipper projects and the reverse proxy', aliases: ['shut'])]
class ShutdownCmd extends BaseCommand
{
    protected function handle(): int
    {

        foreach ($this->configRepo->config->projects as $project) {
            if (!$project->isRunning()) {
                $this->io->writeln("<fg=gray>⏭️  Skipping <info>{$project->name}</info>, not currently running</>");

                continue;
            }

            $this->io->writeln("<fg=gray>⏹️  Stopping <info>{$project->name}</info></>");

            Execute::onShell($project->composeCommand('down'));
        }

        $this->io->newLine();
        $this->io->writeln('<fg=gray>⏹️  Stopping the reverse proxy</>');
        $this->configRepo->caddy->stop();

        return Command::SUCCESS;
    }
}
