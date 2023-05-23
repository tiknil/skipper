<?php

namespace Tiknil\Skipper\Commands;

use Symfony\Component\Yaml\Yaml;
use Tiknil\Skipper\Config\Project;

trait WithProject
{
    protected Project $project;

    protected bool $recursive = true;

    /*
     * Check if the directory belongs to an active skipper project.
     *
     * AUTOMATICALLY invoked by the BaseCommand
     */
    protected function validateProjectDir(): void
    {
        $project = $this->configRepo->config->projectByPath(getcwd(), $this->recursive);

        if (empty($project)) {
            $this->io->warning([
                'Invalid path',
                'The current path does not belong to a valid skipper project',
            ]);

            $this->io->writeln('Use command <info>init</info> to create a new skipper project for this path');

            exit();
        }

        $this->project = $project;
    }

    protected function checkComposeFile(): bool
    {
        if (!file_exists($this->project->composeFilePath())) {
            $this->io->warning('Compose file not found at path '.$this->project->composeFilePath());

            return false;
        }

        $warnings = 0;

        // Load file
        try {
            $data = Yaml::parseFile($this->project->composeFilePath());
        } catch (\Exception $e) {

            $this->io->error(['Error parsing the compose file', $e->getMessage()]);

            return false;
        }

        $network = $data['networks'][$this->configRepo->config->network] ?? [];
        if (
            empty($network)
            || ($network['external'] ?? '') !== true) {

            $this->io->warning([
                'Invalid networks configuration in your compose file',
                "Caddy can't contact your nginx container unless they use a default external container named {$this->configRepo->config->network}",
            ]);

            $this->io->infoText('Add this to your compose file:');
            $this->io->text(<<<EOD
networks:
  default:
    driver: bridge
  {$this->configRepo->config->network}:
    external: true
EOD
            );

            $warnings++;
        }

        $services = $data['services'] ?? [];

        if (!isset($services[$this->project->httpContainer])) {
            $this->io->warning([
                "{$this->project->httpContainer} container is missing from your compose file",
                "Caddy is forwarding HTTP requests to {$this->project->httpContainer}, it should exist with port 80 exposed",
            ]);

            $warnings++;
        }

        $nginxNetworks = $services[$this->project->httpContainer]['networks'] ?? [];

        if (!empty(array_diff(['default', $this->configRepo->config->network], $nginxNetworks))) {
            $this->io->warning([
                "{$this->project->httpContainer} container should be attached to two networks",
                "Add both 'default' and '{$this->configRepo->config->network}' as {$this->project->httpContainer} networks",
            ]);

            $warnings++;
        }

        if (!isset($services[$this->project->phpContainer])) {
            $this->io->warning([
                "{$this->project->phpContainer} container is missing from your compose file",
                "Skipper runs PHP and bash commands on {$this->project->phpContainer}, it should exist",
            ]);

            $warnings++;
        }

        $override = rtrim(dirname($this->project->composeFilePath()), '/').'/docker-compose.override.yml';

        if (file_exists($override)) {
            $this->io->warning([
                "Found override file at $override",
                'It will not be picked up unless you use skipper from the directory the file is located at '.
                '('.dirname($override).")\n".
                'We suggest not using an override file, to avoid different behaviour depending on the directory you are running scripts from',
            ]);

            $warnings++;
        }

        return $warnings === 0;
    }

    protected function checkRunning(): void
    {
        if (!$this->project->isRunning()) {
            $this->io->warning('Project is not running. Use the sail command');

            exit();
        }
    }
}
