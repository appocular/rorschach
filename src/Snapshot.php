<?php

namespace Rorschach;

use Facebook\WebDriver\WebDriver;
use Symfony\Component\Console\Style\StyleInterface;
use Throwable;

class Snapshot
{
    protected $config;
    protected $processor;
    protected $webdriver;
    protected $stitcher;

    public function __construct(
        Config $config,
        CheckpointProcessor $processor,
        WebDriver $webdriver,
        Stitcher $stitcher,
        StyleInterface $io
    ) {
        $this->config = $config;
        $this->processor = $processor;
        $this->webdriver = $webdriver;
        $this->stitcher = $stitcher;
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
                    $this->webdriver->get($this->config->getBaseUrl() . $step->path);
                    if ($step->hide && $selectors = array_filter(array_values($step->hide))) {
                        $this->stitcher->hideElements($selectors);
                    }
                    $this->processor->process($step, $this->stitcher->stitchScreenshot());
                } catch (Throwable $e) {
                    $this->io->error(sprintf(
                        'Error checkpointing "%s": "%s", skipping.',
                        $step->name,
                        $e->getMessage()
                    ));
                }
            }
        } finally {
            $this->webdriver->quit();
            $this->processor->end();
        }
    }
}
