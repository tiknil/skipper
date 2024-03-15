<?php

namespace Tiknil\Skipper\Commands\ProjectCmds;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Tiknil\Skipper\Commands\BaseCommand;
use Tiknil\Skipper\Commands\WithProject;
use Tiknil\Skipper\Utils\Execute;

#[AsCommand(name: 'ide-helper', description: 'Create laravel models definitions for the IDE')]
class IdeHelperCmd extends BaseCommand
{
    use WithProject;

    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'no-pint',
                        '',
                        InputOption::VALUE_NONE,
                    ),
                    new InputOption(
                        'models-folder',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'Models folder to format with Pint',
                        'app/Models'
                    ),
                ])
            );
    }

    protected function handle(): int
    {
        $this->checkRunning();

        $noPint = $this->input->getOption('no-pint');
        $folder = $this->input->getOption('models-folder');

        $ideHelperCmd = [
            ...$this->project->baseCommand(),
            'exec',
            $this->project->phpContainer,
            'php',
            'artisan',
            'ide-helper:models',
            '-W',                   // Write in the model file
            '-R',                    // Reset, remove previous phpdocs to avoid duplicates
        ];

        $result = Execute::onOutput($ideHelperCmd);

        if ($result !== self::SUCCESS) {
            return $result;
        }

        if ($noPint) {
            return $result;
        }

        $pintCmd = [
            ...$this->project->baseCommand(),
            'exec',
            $this->project->phpContainer,
            'vendor/bin/pint',
            $folder,
        ];

        return Execute::onOutput($pintCmd);
    }
}
