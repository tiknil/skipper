<?php

namespace Tiknil\Skipper\Config;

class Config
{
    /**
     * @param  array<string,Project>  $projects
     */
    public function __construct(
        public array $projects = [],
        public string $network = 'skipper_network',
        public string $dockerBaseUrl = 'https://github.com/tiknil/laravel-docker-compose'
    ) {
    }

    public function projectByPath(string $path, bool $recursive = false): Project|null
    {
        while ($path !== '' && $path !== '/') {
            foreach ($this->projects as $project) {
                if ($project->path === $path) {
                    return $project;
                }
            }

            if ($recursive) {
                $path = dirname($path);
            } else {
                $path = '';
            }
        }

        return null;
    }

    public function projectByName(string $name): Project|null
    {
        return $this->projects[$name] ?? null;
    }

    public function projectByHost(string $host): Project|null
    {
        foreach ($this->projects as $project) {
            if ($project->host === $host) {
                return $project;
            }
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'projects' => array_map(fn ($p) => $p->toArray(), $this->projects),
            'network' => $this->network,
            'dockerBaseUrl' => $this->dockerBaseUrl,
        ];
    }
}
