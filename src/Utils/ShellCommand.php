<?php

namespace Tiknil\Skipper\Utils;

use Symfony\Component\Process\Process;

class ShellCommand
{
    private bool $useTty = false;

    private bool $useShellIntegration = false;

    private bool $showOutput = true;

    private bool $showLog = true;

    public function __construct(
        bool $useTty = false,
        bool $useShellIntegration = false,
        bool $showOutput = true,
        bool $showLog = true
    ) {
        $this->useTty = $useTty;
        $this->useShellIntegration = $useShellIntegration;
        $this->showOutput = $showOutput;
        $this->showLog = $showLog;
    }

    public static function new(): static
    {
        return new static;
    }

    public function run(array $cmd): int
    {
        if ($this->showLog) {
            $this->log($cmd);
        }

        $process = $this->useShellIntegration
            ? Process::fromShellCommandline(implode(' ', $cmd))
            : new Process($cmd);

        if ($this->showOutput) {
            $process->enableOutput();
        } else {
            $process->disableOutput();
        }

        $process->setTty($this->useTty);

        $process->setTimeout($this->getTimeout() ?? 0);

        $result = $process->run(function ($type, $buffer) {
            if ($this->showOutput) {
                echo $buffer;
            }
        });

        Globals::$output->writeln('');

        return $result;
    }

    /**
     * Use tty mode when running commands.
     * TTY allows for interactive input and formatted forwarded output.
     */
    public function useTty(bool $useTty = true): static
    {
        $this->useTty = $useTty;

        return $this;
    }

    /**
     * Use the shell integration when running commands.
     * This allows for better handling of shell features, such as pipes and redirection.
     */
    public function useShellIntegration(bool $useShellIntegration = true): static
    {
        $this->useShellIntegration = $useShellIntegration;

        return $this;
    }

    public function showOutput(bool $showOutput = true): static
    {
        $this->showOutput = $showOutput;

        return $this;
    }

    public function showLog(bool $showLog = true): static
    {
        $this->showLog = $showLog;

        return $this;
    }

    private function getTimeout(): ?int
    {
        if ($this->useTty || $this->useShellIntegration) {
            return null; // no timeout
        }

        return 60;
    }

    private function log(array $cmd)
    {
        Globals::$output->writeln('<fg=gray> 💻  '.implode(' ', $cmd)."</>\n");
    }
}
