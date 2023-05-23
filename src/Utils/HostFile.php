<?php

namespace Tiknil\Skipper\Utils;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class HostFile
{
    private TiknilStyle $io;

    public function __construct(private string $host, private string $ip = '127.0.0.1')
    {
        $this->io = Globals::$io;
    }

    public static function for(string $host, string $ip = '127.0.0.1'): self
    {
        return new self($host, $ip);
    }

    public function check(): bool
    {
        return $this->findHostLine($this->readFile()) !== null;
    }

    public function requestAdd(): void
    {
        if (!$this->add()) {
            $this->io->writeln('📝 You can add the record manually to your /etc/hosts '.
                "file or try again with command <info>skipper host $this->host --ip $this->ip</info>"
            );
        }
    }

    public function add(): bool
    {
        $this->io->writeln("🔀 Mapping <comment>$this->host</comment> to <comment>$this->ip</comment>");

        $lines = $this->readFile();

        $hostLine = $this->findHostLine($lines);

        if ($hostLine !== null) {
            $this->io->newLine();
            $this->io->writeln("🧐 $this->host already found on your hosts file at line $hostLine:");
            $this->io->newLine();
            $this->io->writeln($lines[$hostLine]);

            if (!$this->io->confirm('Do you want to overwrite it?')) {
                return false;
            }

            array_splice($lines, $hostLine, 1);
        }

        $lines[] = "$this->ip\t$this->host";

        if ($this->writeFile($lines) !== Command::SUCCESS) {
            $this->io->error([
                'Error updating your host file',
                'Check your file content and, if required, restore the file from the backup',
            ]);

            $this->io->definitionList(
                ['cat /etc/hosts' => 'Check your file content'],
                ['sudo cp /etc/hosts.bkp /etc/hosts' => 'Restore the backup']
            );

            return false;
        } else {

            $this->io->writeln("✅ <comment>$this->host</comment> mapped successfully to <comment>$this->ip</comment>");

        }

        $this->io->newLine();

        return true;

    }

    public function requestRemove(): void
    {

        if (!$this->remove()) {
            $this->io->writeln('You can remove the record manually to your /etc/hosts file'.
                " or try again with command <info>skipper host $this->host --remove</info>"
            );
        }
    }

    public function remove(): bool
    {
        $this->io->writeln("Removing <comment>$this->host</comment> from /etc/hosts");

        $lines = $this->readFile();

        $hostLine = $this->findHostLine($lines);

        if ($hostLine === null) {
            $this->io->writeln("$this->host not found on your hosts file");

            return true;
        }

        array_splice($lines, $hostLine, 1);

        if ($this->writeFile($lines) !== Command::SUCCESS) {
            $this->io->error([
                'Error updating your host file',
                'Check your file content and, if required, restore the file from the backup',
            ]);

            $this->io->definitionList(
                ['cat /etc/hosts' => 'Check your file content'],
                ['sudo cp /etc/hosts.bkp /etc/hosts' => 'Restore the backup']
            );

            return false;
        } else {

            $this->io->writeln("<comment>$this->host</comment> removed successfully from your /etc/hosts file");
        }

        $this->io->newLine();

        return true;

    }

    private function readFile(): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim(file_get_contents('/etc/hosts')));

        return $lines;
    }

    private function findHostLine(array $lines): int|null
    {
        foreach ($lines as $i => $line) {
            if (str_starts_with($line, '#')) {
                continue;
            }

            if (str_ends_with($line, $this->host)) {
                return $i;
            }
        }

        return null;
    }

    private function writeFile(array $lines): int
    {
        $this->io->writeln('We need <info>sudo</info> permissions to create a backup copy of your hosts file and to remove the host');
        $this->io->writeln('🔐 You may be prompted for your password');

        if (Execute::onShell(['sudo', 'cp', '/etc/hosts', '/etc/hosts.bkp'], false) !== Command::SUCCESS) {
            $this->io->error('Error creating hosts backup file.');

            return false;
        }

        $this->io->writeln('📦 Backup file <comment>/etc/hosts.bkp</comment> created successfully');

        $inputStream = new InputStream();

        $process = new Process(['sudo', 'tee', '/etc/hosts']);
        $process->setInput($inputStream);

        $process->start();
        foreach ($lines as $line) {
            $inputStream->write($line."\n");
        }
        $inputStream->close();

        return $process->wait();
    }
}
