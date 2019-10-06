<?php

namespace Rorschach;

use Rorschach\Helpers\Output;
use Throwable;

class Snapshot
{
    /**
     * @var \Rorschach\Config
     */
    protected $config;

    /**
     * @var \Rorschach\CheckpointFetcher
     */
    protected $fetcher;

    /**
     * @var \Rorschach\CheckpointProcessor
     */
    protected $processor;

    /**
     * @var \Rorschach\Helpers\Output
     */
    protected $output;

    public function __construct(
        Config $config,
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output
    ) {
        $this->config = $config;
        $this->fetcher = $fetcher;
        $this->processor = $processor;
        $this->output = $output;
    }
    /**
     * Run snapshot and submit checkpoints to Appocular.
     */
    public function run()
    {
        try {
            foreach ($this->config->getSteps() as $step) {
                try {
                    if (!$this->config->getQuiet()) {
                        $this->output->message(sprintf('Checkpointing "%s"...', $step->name));
                    }
                    $this->processor->process($step, $this->fetcher->fetch($step));
                } catch (Throwable $e) {
                    $this->output->error(sprintf(
                        'Error checkpointing "%s": "%s", skipping.',
                        $step->name,
                        $e->getMessage()
                    ));
                }
            }
        } finally {
            $this->fetcher->end();
            $output = $this->processor->end();
            if ($output) {
                $this->output->newLine();
                foreach ($output as $line) {
                    $this->output->message($line);
                }
            }
        }
    }
}
