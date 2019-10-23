<?php

namespace Rorschach\Helpers;

use Symfony\Component\Process\Process;

class WorkerProcess
{
    /**
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    public function __construct($argv, $checkpoints)
    {
        $argv = array_merge($argv, ['--worker', '--ansi']);
        $this->process = new Process($argv);
        $this->process->setInput(\serialize($checkpoints));
        $this->process->start();
    }

    public function isRunning()
    {
        return $this->process->isRunning();
    }

    public function getIncrementalOutput()
    {
        return $this->process->getIncrementalOutput();
    }

    public function getIncrementalErrorOutput()
    {
        return $this->process->getIncrementalErrorOutput();
    }

    public function getExitCode()
    {
        return $this->process->getExitCode();
    }
}
