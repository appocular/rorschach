<?php

namespace Rorschach;

use Rorschach\CheckpointProcessor;
use Rorschach\Helpers\Output;
use Rorschach\Helpers\WorkerFactory;
use RuntimeException;
use Throwable;

class Multiplexer
{
    /**
     * @var \Rorschach\Config
     */
    protected $config;

    /**
     * @var \Rorschach\Helpers\Output
     */
    protected $output;

    /**
     * @var \Rorschach\Helpers\WorkerFactory
     */
    protected $workerFactory;

    /**
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
    public function run()
    {
        $success = true;
        $numWorkers = $this->config->getWorkers();
        $workerCheckpoints = array_fill(0, $numWorkers, []);

        foreach ($this->config->getCheckpoints() as $num => $checkpoint) {
            $workerCheckpoints[$num % $numWorkers][] = $checkpoint;
        }

        // Filter out empty workers.
        $workerCheckpoints = array_filter($workerCheckpoints);

        $workers = [];
        $this->output->info(sprintf('Starting %d workers', count($workerCheckpoints)));
        foreach ($workerCheckpoints as $num => $checkpoints) {
            $workers[] = $this->workerFactory->create($checkpoints);
        }

        do {
            $running = false;
            foreach ($workers as $num => $worker) {
                $running = $running || $worker->isRunning();
                $output = $worker->getIncrementalOutput();
                if ($output) {
                    foreach (explode("\n", trim($output)) as $line) {
                        $this->output->numberedLine(($num + 1), $line);
                    }
                }

                $output = $worker->getIncrementalErrorOutput();
                if ($output) {
                    $this->output->error(sprintf('Worker %d error, output:', $num + 1));
                    foreach (explode("\n", trim($output)) as $line) {
                        $this->output->numberedLine(($num + 1), $line);
                    }
                }
            }

            // Only sleep if we still have running workers.
            if ($running) {
                usleep(500000);
            }
        } while ($running);

        $this->output->info('Done');
        $this->processor->summarize();

        foreach ($workers as $num => $worker) {
            $success = $success && ($worker->getExitCode() == 0);
        }

        return $success;
    }
}
