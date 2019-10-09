<?php

namespace Rorschach;

use Rorschach\Helpers\Output;
use Throwable;

class Worker
{
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

    /**
     * @var \Rorschach\Step[]
     */
    protected $steps;

    public function __construct(
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output,
        string $data
    ) {
        $this->fetcher = $fetcher;
        $this->processor = $processor;
        $this->output = $output;
        $this->steps = \unserialize($data);

        if (empty($this->steps)) {
            $this->output->error('Error, no steps on STDIN');
        }
    }
    /**
     * Run snapshot and submit checkpoints to Appocular.
     */
    public function run()
    {
        try {
            $this->output->debug('Starting worker');
            foreach ($this->steps as $step) {
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
            $this->output->debug('Ending worker, closing fetcher...');
            $this->fetcher->end();

            $this->output->debug('Closing processor...');
            $this->processor->end();

            $this->output->debug('Done');
        }
    }
}
