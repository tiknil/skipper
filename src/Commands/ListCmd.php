<?php

namespace Tiknil\Skipper\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'list', description: 'List all skipper registered projects')]
class ListCmd extends BaseCommand
{
    protected function handle(): int
    {
        $data = array_map(fn ($p) => [$p->isRunning() ? 'Yes' : 'No', $p->name, $p->host, $p->path], $this->configRepo->config->projects);

        $this->io->table(['Running', 'Name', 'Host', 'Path'], $data);

        if (empty($data)) {
            $this->io->text('There are no projects yet');
        }

        return Command::SUCCESS;
    }
}
