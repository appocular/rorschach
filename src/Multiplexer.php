<?php

declare(strict_types=1);

namespace Rorschach;

use Rorschach\Helpers\Output;
use Rorschach\Helpers\WorkerFactory;

class Multiplexer
{
    /**
     * Our configuration.
     *
     * @var \Rorschach\Config
     */
    protected $config;

    /**
     * Our output.
     *
     * @var \Rorschach\Helpers\Output
     */
    protected $output;

    /**
     * Factory for workers.
     *
     * @var \Rorschach\Helpers\WorkerFactory
     */
    protected $workerFactory;

    /**
     * The processor for checkpoints.
     *
     * @var \Rorschach\CheckpointProcessor
     */
    protected $processor;

    public function __construct(
        Config $config,
        Output $output,
        WorkerFactory $workerFactory,
        CheckpointProcessor $processor
    ) {
        $this->config = $config;
        $this->output = $output;
        $this->workerFactory = $workerFactory;
        $this->processor = $processor;
    }
    /**
     * Run snapshot and submit checkpoints to Appocular.
     */
    public function run(): bool
    {
        $success = true;
        $numWorkers = $this->config->getWorkers();
        $workerCheckpoints = \array_fill(0, $numWorkers, []);

        $checkpoints = $this->config->getCheckpoints();

        foreach ($this->config->getVariants() as $variant) {
            $checkpoints = $variant->getVariations($checkpoints);
        }

        foreach ($checkpoints as $num => $checkpoint) {
            $workerCheckpoints[$num % $numWorkers][] = $checkpoint;
        }

        // Filter out empty workers.
        $workerCheckpoints = \array_filter($workerCheckpoints);

        $workers = [];
        $this->output->info(\sprintf('Starting %d workers', \count($workerCheckpoints)));

        foreach ($workerCheckpoints as $num => $checkpoints) {
            $workers[] = $this->workerFactory->create($checkpoints);
        }

        do {
            $running = false;

            foreach ($workers as $num => $worker) {
                $running = $running || $worker->isRunning();
                $output = $worker->getIncrementalOutput();

                if ($output) {
                    foreach (\explode("\n", \trim($output)) as $line) {
                        $this->output->numberedLine(($num + 1), $line);
                    }
                }

                $output = $worker->getIncrementalErrorOutput();

                if (!$output) {
                    continue;
                }

                $this->output->error(\sprintf('Worker %d error, output:', $num + 1));

                foreach (\explode("\n", \trim($output)) as $line) {
                    $this->output->numberedLine(($num + 1), $line);
                }
            }

            // Only sleep if we still have running workers.
            if (!$running) {
                continue;
            }

            \usleep(500000);
        } while ($running);

        $this->output->info('Done');
        $this->processor->summarize();

        foreach ($workers as $worker) {
            $success = $success && ($worker->getExitCode() === 0);
        }

        return $success;
    }
}
