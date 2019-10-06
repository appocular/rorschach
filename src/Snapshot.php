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
            $this->output->debug('Starting snapshot');
            foreach ($this->config->getSteps() as $step) {
                try {
                    $this->output->message(sprintf('Checkpointing "%s"...', $step->name));
                    $this->output->info('Taking screenshot');
                    $screenshot = $this->fetcher->fetch($step);

                    $this->output->info('Processing screenshot');
                    $this->processor->process($step, $screenshot);

                    $this->output->info('Done');
                } catch (Throwable $e) {
                    $this->output->error(sprintf(
                        'Error checkpointing "%s": "%s", skipping.',
                        $step->name,
                        $e->getMessage()
                    ));
                }
            }
        } finally {
            $this->output->debug('Ending snapshot, closing fetcher...');
            $this->fetcher->end();

            $this->output->debug('Ending snapshot, closing processor...');
            $this->processor->end();

            $this->output->debug('Done');
        }
    }
}
