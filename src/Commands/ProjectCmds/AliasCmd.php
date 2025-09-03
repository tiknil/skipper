<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\HostFile;

#[AsCommand(name: 'alias', description: 'Manage URL alias to the project. Use skipper info to see all the registered aliases')]
class AliasCmd extends BaseCommand
{
    use WithProject;

    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'remove',
                        'r',
                        InputOption::VALUE_NONE,
                        'Remove the URL alias'
                    ),
                ])
            );

        // Arguments must be set after the definition
        $this->addArgument('url', InputArgument::REQUIRED, 'The URL alias to manage');

        $this->addUsage('"*.domain.localhost"');
        $this->addUsage('"*.domain.localhost" --remove');

    }

    protected function handle(): int
    {
        $url = $this->input->getArgument('url');
        $remove = $this->input->getOption('remove');

        if ($remove) {
            return $this->removeAlias($url);
        } else {
            return $this->addAlias($url);
        }
    }

    private function removeAlias(string $url): int
    {
        $existingAliases = $this->project->hostAlias;
        // false if the url is missing from the aliases, the index otherwise
        $key = array_search($url, $existingAliases);

        $this->io->writeln("<comment>Removing alias $url</comment>");

        if ($key === false) {
            $this->io->newLine();
            $this->io->writeln("<comment>Alias $url not found, skipping</comment>");

            return Command::SUCCESS;
        }
        unset($existingAliases[$key]);

        $this->project->hostAlias = array_values($existingAliases);
        $this->configRepo->updateProject($this->project, $this->project->name);

        $this->io->success("$url removed successfully as alias for {$this->project->name}");

        $confirm = $this->io->confirm("Do you want to remove $url from the /etc/hosts file?");

        if (!$confirm) {
            return Command::SUCCESS;
        }

        HostFile::for($url)->requestRemove();

        return Command::SUCCESS;
    }

    private function addAlias(string $url): int
    {
        $existingAliases = $this->project->hostAlias;
        // false if the url is missing from the aliases, the index otherwise
        $key = array_search($url, $existingAliases);

        $this->io->writeln("<comment>Adding alias $url</comment>");

        if ($key !== false) {
            $this->io->newLine();
            $this->io->writeln("<comment>Alias $url already registered, skipping</comment>");

            return Command::SUCCESS;
        }

        $existingAliases[] = $url;

        $this->project->hostAlias = array_values($existingAliases);
        $this->configRepo->updateProject($this->project, $this->project->name);

        $this->io->success("$url registered successfully as alias for {$this->project->name}");

        $confirm = $this->io->confirm("Do you want to add $url to the /etc/hosts file?");

        if (!$confirm) {
            return Command::SUCCESS;
        }

        HostFile::for($url)->requestAdd();

        return Command::SUCCESS;
    }
}
