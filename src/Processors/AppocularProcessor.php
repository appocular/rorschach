<?php

namespace Rorschach\Processors;

use Rorschach\Appocular;
use Rorschach\CheckpointProcessor;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Step;

class AppocularProcessor implements CheckpointProcessor
{
    /**
     * @var \Rorschach\Appocular\Batch
     */
    protected $batch;

    /**
     * @var \Rorschach\Config
     */
    protected $config;

    /**
     * @var \Rorschach\Helpers\Output
     */
    protected $output;

    public function __construct(Config $config, Appocular $appocular, Output $output)
    {
        $output->debug('Creating batch.');
        $this->batch = $appocular->startBatch($config->getSha(), $config->getHistory());
        $this->config = $config;
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Step $step, string $pngData): void
    {
        $this->output->debug("Submitting checkpoint \"{$step->name}\".");
        $this->batch->checkpoint($step->name, $pngData);
    }

    /**
     * {@inheritdoc}
     */
    public function end(): void
    {
        $this->batch->close();
    }

    /**
     * {@inheritdoc}
     */
    public function summarize(): void
    {
        $this->output->newLine();
        $this->output->message("Verify snapshot at https://" . $this->config->getBase() .
                               '/' . $this->config->getSha());
    }
}
