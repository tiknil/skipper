<?php

namespace Tiknil\Skipper;

use Tiknil\Skipper\Commands\HostCmd;
use Tiknil\Skipper\Commands\InitCmd;
use Tiknil\Skipper\Commands\ListCmd;
use Tiknil\Skipper\Commands\ManCmd;
use Tiknil\Skipper\Commands\ProjectCmds\ArtisanCmd;
use Tiknil\Skipper\Commands\ProjectCmds\BackupCmd;
use Tiknil\Skipper\Commands\ProjectCmds\BashCmd;
use Tiknil\Skipper\Commands\ProjectCmds\CheckCmd;
use Tiknil\Skipper\Commands\ProjectCmds\ComposeCmd;
use Tiknil\Skipper\Commands\ProjectCmds\ComposerCmd;
use Tiknil\Skipper\Commands\ProjectCmds\DockCmd;
use Tiknil\Skipper\Commands\ProjectCmds\DockerBaseCmd;
use Tiknil\Skipper\Commands\ProjectCmds\EditCmd;
use Tiknil\Skipper\Commands\ProjectCmds\InfoCmd;
use Tiknil\Skipper\Commands\ProjectCmds\RestoreCmd;
use Tiknil\Skipper\Commands\ProjectCmds\RmCmd;
use Tiknil\Skipper\Commands\ProjectCmds\SailCmd;
use Tiknil\Skipper\Commands\ProjectCmds\SyncCmd;
use Tiknil\Skipper\Commands\ProjectCmds\TinkerCmd;
use Tiknil\Skipper\Commands\ProxyCmd\CertsCmd;
use Tiknil\Skipper\Commands\ProxyCmd\ConfigCmd;
use Tiknil\Skipper\Commands\ProxyCmd\DownCmd;
use Tiknil\Skipper\Commands\ProxyCmd\RestartCmd;
use Tiknil\Skipper\Commands\ProxyCmd\ShowCmd;
use Tiknil\Skipper\Commands\ProxyCmd\UpCmd;
use Tiknil\Skipper\Commands\ProxyCmd\UpdateCmd;
use Tiknil\Skipper\Commands\ShutdownCmd;

class CliApplication extends \Symfony\Component\Console\Application
{
    public function registerCommands(): void
    {
        // Helpers
        $this->add(new ListCmd());
        $this->add(new ShutdownCmd());
        $this->add(new HostCmd());
        $this->add(new ManCmd());

        // Project management
        $this->add(new InitCmd());
        $this->add(new SailCmd());
        $this->add(new DockCmd());
        $this->add(new EditCmd());
        $this->add(new RmCmd());
        $this->add(new InfoCmd());
        $this->add(new CheckCmd());
        $this->add(new ComposeCmd());
        $this->add(new DockerBaseCmd());

        // Project utils
        $this->add(new BashCmd());
        $this->add(new ComposerCmd());
        $this->add(new ArtisanCmd());
        $this->add(new TinkerCmd());
        $this->add(new BackupCmd());
        $this->add(new RestoreCmd());
        $this->add(new SyncCmd());

        // Caddy
        $this->add(new CertsCmd());
        $this->add(new ShowCmd());
        $this->add(new UpCmd());
        $this->add(new DownCmd());
        $this->add(new RestartCmd());
        $this->add(new ConfigCmd());
        $this->add(new UpdateCmd());
    }
}
