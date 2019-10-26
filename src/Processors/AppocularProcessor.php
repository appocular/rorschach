<?php

namespace Rorschach\Processors;

use Rorschach\Appocular;
use Rorschach\CheckpointProcessor;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Checkpoint;

class AppocularProcessor implements CheckpointProcessor
{
    /**
     * @var \Rorschach\Appocular\Batch
     */
    protected $batch;

    /**
     * @var \Rorschach\Appocular
     */
    protected $appocular;

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
        $this->config = $config;
        $this->appocular = $appocular;
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Checkpoint $checkpoint, string $pngData): void
    {
        if (!$this->batch) {
            $this->output->debug('Creating batch.');
            $this->batch = $this->appocular->startBatch(
                $this->config->getSha(),
                $this->config->getHistory()
            );
        }
        $this->output->debug("Submitting checkpoint \"{$checkpoint->name}\".");
        $this->batch->checkpoint($checkpoint->name, $pngData, $checkpoint->meta);
    }

    /**
     * {@inheritdoc}
     */
    public function end(): void
    {
        if ($this->batch) {
            $this->batch->close();
        }
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
