<?php

declare(strict_types=1);

namespace Rorschach\Helpers;

use Symfony\Component\Process\Process;

class WorkerProcess
{
    /**
     * The worker process.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * @param array<string> $argv
     * @param array<\Rorschach\Checkpoint> $checkpoints
     */
    public function __construct(array $argv, array $checkpoints)
    {
        $argv = \array_merge($argv, ['--worker', '--ansi']);
        $this->process = new Process($argv);
        $this->process->setInput(\serialize($checkpoints));
        $this->process->start();
    }

    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }

    public function getIncrementalOutput(): string
    {
        return $this->process->getIncrementalOutput();
    }

    public function getIncrementalErrorOutput(): string
    {
        return $this->process->getIncrementalErrorOutput();
    }

    public function getExitCode(): ?int
    {
        return $this->process->getExitCode();
    }
}
