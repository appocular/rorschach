<?php

declare(strict_types=1);

namespace Rorschach\Helpers;

class WorkerFactory
{
    /**
     * Array of command and options.
     *
     * @var array<string>
     */
    protected $argv;

    /**
     * @param array<string> $argv
     */
    public function __construct(array $argv)
    {
        $this->argv = $argv;
    }

    /**
     * @param array<\Rorschach\Checkpoint> $checkpoints
     */
    public function create(array $checkpoints): WorkerProcess
    {
        return new WorkerProcess($this->argv, $checkpoints);
    }
}
