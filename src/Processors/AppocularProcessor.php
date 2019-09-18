<?php

namespace Rorschach\Processors;

use Rorschach\Appocular;
use Rorschach\CheckpointProcessor;
use Rorschach\Config;
use Rorschach\Step;

class AppocularProcessor implements CheckpointProcessor
{

    /**
     * @var \Rorschach\Config
     */
    protected $config;

    public function __construct(Config $config, Appocular $appocular)
    {
        $this->batch = $appocular->startBatch($config->getSha(), $config->getHistory());
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Step $step, string $pngData) : void
    {
        $this->batch->checkpoint($step->name, $pngData);
    }

    /**
     * {@inheritdoc}
     */
    public function end() : ?array
    {
        $this->batch->close();
        return ["Verify snapshot at https://stopgap." . $this->config->getBase() .
                '/snapshot/' . $this->config->getSha()];
    }
}
