<?php

declare(strict_types=1);

namespace Rorschach;

use Rorschach\Helpers\Output;
use Throwable;

class Worker
{
    /**
     * Fetcher to use.
     *
     * @var \Rorschach\CheckpointFetcher
     */
    protected $fetcher;

    /**
     * Processor to use.
     *
     * @var \Rorschach\CheckpointProcessor
     */
    protected $processor;

    /**
     * Output to use.
     *
     * @var \Rorschach\Helpers\Output
     */
    protected $output;

    /**
     * Checkpoints to process.
     *
     * @var array<\Rorschach\Checkpoint>
     */
    protected $checkpoints;

    public function __construct(
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output,
        string $data
    ) {
        $this->fetcher = $fetcher;
        $this->processor = $processor;
        $this->output = $output;
        $this->checkpoints = \unserialize($data);
    }
    /**
     * Run snapshot and submit checkpoints to Appocular.
     */
    public function run(): bool
    {
        $success = false;

        if (!$this->checkpoints) {
            $this->output->error('Error, no checkpoints on STDIN');

            return false;
        }

        try {
            $this->output->debug('Starting worker');
            $success = true;

            foreach ($this->checkpoints as $checkpoint) {
                try {
                    $this->output->message(\sprintf('Checkpointing "%s"...', $checkpoint->name));
                    $this->output->info('Taking screenshot');
                    $screenshot = $this->fetcher->fetch($checkpoint);

                    $this->output->info('Processing screenshot');
                    $this->processor->process($checkpoint, $screenshot);

                    $this->output->info('Done');
                } catch (Throwable $e) {
                    $success = false;
                    $this->output->error(\sprintf(
                        'Error checkpointing "%s": "%s", skipping.',
                        $checkpoint->name,
                        $e->getMessage(),
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

        return $success;
    }
}
