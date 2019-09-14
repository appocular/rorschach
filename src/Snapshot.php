<?php

namespace Rorschach;

use Symfony\Component\Console\Style\StyleInterface;
use Throwable;

class Snapshot
{
    protected $config;
    protected $fetcher;
    protected $processor;

    public function __construct(
        Config $config,
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        StyleInterface $io
    ) {
        $this->config = $config;
        $this->fetcher = $fetcher;
        $this->processor = $processor;
        $this->io = $io;
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
                        $this->io->text(sprintf('Checkpointing "%s"...', $step->name));
                    }
                    $this->processor->process($step, $this->fetcher->fetch($step));
                } catch (Throwable $e) {
                    $this->io->error(sprintf(
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
                $this->io->newLine();
                foreach ($output as $line) {
                    $this->io->text($line);
                }
            }
        }
    }
}
