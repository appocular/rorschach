<?php

namespace Rorschach;

use Rorschach\Helpers\Output;
use Rorschach\Helpers\WorkerFactory;
use Rorschach\CheckpointProcessor;
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
        $numWorkers = $this->config->getWorkers();
        $workerSteps = array_fill(0, $numWorkers, []);

        foreach ($this->config->getSteps() as $num => $step) {
            $workerSteps[$num % $numWorkers][] = $step;
        }

        // Filter out empty workers.
        $workerSteps = array_filter($workerSteps);

        $workers = [];
        $this->output->info(sprintf('Starting %d workers', count($workerSteps)));
        foreach ($workerSteps as $num => $steps) {
            $workers[] = $this->workerFactory->create($steps);
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
    }
}
