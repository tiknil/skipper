<?php

namespace Tiknil\Skipper\Config;

use Symfony\Component\Yaml\Yaml;
use Tiknil\Skipper\Utils\Globals;

class Repository
{
    public Config $config;

    public Proxy $caddy;

    public function __construct()
    {
        $this->config = $this->loadConfig();
        $this->caddy = $this->loadCaddy();
    }

    private function loadConfig(): Config
    {
        // Checks if the file exists
        if (!file_exists(self::filePath())) {

            Globals::$io->infoText('Skipper configuration file not found. Creating now');

            $this->config = new Config;
            $this->updateConfig();

            Globals::$io->infoText('Configuration file created at '.$this->filePath());

            return $this->config;

        } else {
            try {
                $data = Yaml::parseFile($this->filePath());

                $config = new Config;

                if (is_array($data['projects'] ?? false)) {

                    $config->projects = array_map(
                        fn ($proj) => Project::from($proj),
                        $data['projects']
                    );

                } else {
                    Globals::$io->warning('Projects key missing from config files');
                }

                if (is_string($data['network'] ?? false)) {
                    $config->network = $data['network'];
                } else {
                    Globals::$io->warning('Network key missing from config files. Using default');
                }

                if (is_string($data['dockerBaseUrl'] ?? false)) {
                    $config->dockerBaseUrl = $data['dockerBaseUrl'];
                }

                return $config;

            } catch (\Exception $e) {
                Globals::$io->error('Unable to read config file located in '.$this->filePath());
                Globals::$io->error($e->getMessage());
                exit();
            }
        }
    }

    private function loadCaddy(): Proxy
    {
        $caddy = new Proxy;

        if (!$caddy->check()) {
            Globals::$io->info('Caddy files are missing. Setting them up now');

            $caddy->setup();

            $caddy->writeCaddyfile($this->config);

            Globals::$io->info('Done. Caddy is ready to run');

        }

        return $caddy;
    }

    public function updateProject(Project $project, string $fromName): void
    {
        if ($fromName !== $project->name) {
            unset($this->config->projects[$fromName]);
        }

        $this->config->projects[$project->name] = $project;

        $this->updateConfig();

        $this->caddy->writeCaddyfile($this->config);
        $this->caddy->reload();
    }

    public function updateConfig(): void
    {
        if (!file_exists($this->fileDir())) {
            mkdir($this->fileDir(), 0777, true);
        }

        $result = file_put_contents($this->filePath(), Yaml::dump($this->config->toArray(), 4));

        if (!$result) {
            exit();
        }
    }

    private function fileDir(): string
    {
        return rtrim(getenv('HOME'), '/').'/.skipper';
    }

    private function fileName(): string
    {
        return 'config.yaml';
    }

    private function filePath(): string
    {
        return $this->fileDir().'/'.$this->fileName();
    }
}
