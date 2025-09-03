<?php

namespace Tiknil\Skipper\Utils;

use Symfony\Component\Console\Command\Command;
use Tiknil\Skipper\Config\Config;

class DockerBase
{
    private TiknilStyle $io;

    private Config $config;

    public function __construct(private string $path, private string $folder = 'docker')
    {
        $this->path = rtrim($this->path, '/');
        $this->folder = trim($this->folder, '/');
        $this->io = Globals::$io;
        $this->config = Globals::$configRepo->config;
    }

    public static function install(string $path, string $folder = 'docker'): void
    {
        (new self($path, $folder))->performInstall();
    }

    public function performInstall()
    {
        $filePath = "$this->path/$this->folder";
        $this->io->writeln(["🚧 Installing base docker configuration into <info>{$this->folder}/</info>"]);

        if (file_exists($filePath.'/')) {

            $this->io->warning(["Folder {$this->folder} already exists.", 'All its content will be deleted and replaced']);

            if ($this->io->confirm('Proceed anyway?')) {

                ShellCommand::new()->run(['rm', '-r', $filePath]);
            } else {
                $this->io->writeln('Operation canceled. The base docker configuration will <info>not</info> be installed');

                return;
            }
        }

        $result = ShellCommand::new()->useTty()->run(['git', 'clone', $this->config->dockerBaseUrl, $filePath]);

        if ($result !== Command::SUCCESS) {
            return;
        }

        ShellCommand::new()->run(['cp', $filePath.'/.env.base', $filePath.'/.env']);

        ShellCommand::new()->run(['rm', '-rf', $filePath.'/.git', $filePath.'/.env.base']);
    }
}
