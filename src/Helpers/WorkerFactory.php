<?php

namespace Rorschach\Helpers;



class WorkerFactory
{
    /**
     * @var string[]
     */
    protected $argv;

    public function __construct($argv)
    {
        $this->argv = $argv;
    }

    public function create($checkpoints)
    {
        return new WorkerProcess($this->argv, $checkpoints);
    }
}
