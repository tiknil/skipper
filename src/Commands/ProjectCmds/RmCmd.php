<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;
use Tiknil\Skipper\Utils\HostFile;

#[AsCommand(name: 'rm', description: 'Removes the skipper project')]
class RmCmd extends BaseCommand
{
    use WithProject;

    protected function prepare()
    {
        $this->io->infoText('Removing the skipper project');
    }

    protected function handle(): int
    {
        Execute::onShell($this->project->composeCommand('down'));

        $this->io->definitionList(...$this->project->definitionList());

        $this->io->writeln('Skipper will remove all its references to this project, but no file at this path will be edited.');
        $confirm = $this->io->confirm("Proceed with {$this->project->name} deletion?", false);

        if (!$confirm) {
            return Command::SUCCESS;
        }

        Execute::onShell($this->project->composeCommand('rm'));

        unset($this->configRepo->config->projects[$this->project->name]);
        $this->configRepo->updateConfig();

        $this->configRepo->caddy->writeCaddyfile($this->configRepo->config);
        $this->configRepo->caddy->reload();

        if ($this->io->confirm("Remove {$this->project->host} from your /etc/hosts file?")) {

            HostFile::for($this->project->host)->requestRemove();
        }

        return Command::SUCCESS;

    }
}
