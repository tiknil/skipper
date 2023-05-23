<?php

namespace Tiknil\Skipper\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tiknil\Skipper\Config\Repository;
use Tiknil\Skipper\Utils\Globals;
use Tiknil\Skipper\Utils\TiknilStyle;

abstract class BaseCommand extends Command
{
    protected InputInterface $input;

    protected OutputInterface $output;

    protected TiknilStyle $io;

    protected Repository $configRepo;

    protected bool $withConfig = true;

    abstract protected function handle(): int;

    protected function prepare()
    {
    }

    /*
     * Wrapper of the command handle function, initializing the application data
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new TiknilStyle($input, $output);

        $this->input = $input;
        Globals::$input = $this->input;

        $this->output = $output;
        Globals::$output = $this->output;

        $this->io = $io;
        Globals::$io = $this->io;

        if ($this->withConfig) {
            $this->configRepo = new Repository();
            Globals::$configRepo = $this->configRepo;
        }

        $this->prepare();

        if (method_exists($this, 'validateProjectDir')) {
            $this->validateProjectDir();
        }

        return $this->handle();

    }
}
