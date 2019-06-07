<?php

namespace Rorschach\Processors;

use Rorschach\Appocular;
use Rorschach\CheckpointProcessor;
use Rorschach\Config;
use Rorschach\Step;

class AppocularProcessor implements CheckpointProcessor
{

    public function __construct(Config $config, Appocular $appocular)
    {
        // todo: get branch name and repo...
        $this->batch = $appocular->startBatch($config->getSha(), $config->getHistory());
    }

    /**
     * {@inheritdoc}
     */
    public function process(Step $step, $pngData) : void
    {
        $this->batch->checkpoint($step->name, $pngData);
    }

    /**
     * {@inheritdoc}
     */
    public function end() : void
    {
        $this->batch->close();
    }
}
