<?php

namespace Rorschach;

use Facebook\WebDriver\WebDriver;
use Symfony\Component\Console\Style\StyleInterface;
use Throwable;

class Snapshot
{
    protected $config;
    protected $appocular;
    protected $webdriver;
    protected $stitcher;

    public function __construct(
        Config $config,
        Appocular $appocular,
        WebDriver $webdriver,
        Stitcher $stitcher,
        StyleInterface $io
    ) {
        $this->config = $config;
        $this->appocular = $appocular;
        $this->webdriver = $webdriver;
        $this->stitcher = $stitcher;
        $this->io = $io;
    }
    /**
     * Run snapshot and submit checkpoints to Appocular.
     */
    public function run()
    {
        $batch = null;
        try {
            // todo: get branch name and repo...
            $batch = $this->appocular->startBatch($this->config->getSha(), $this->config->getHistory());

            foreach ($this->config->getSteps() as $name => $path) {
                try {
                    $this->webdriver->get($this->config->getBaseUrl() . $path);
                    $batch->checkpoint($name, $this->stitcher->stitchScreenshot());
                } catch (Throwable $e) {
                    $this->io->error(sprintf('Error checkpointing "%s": "%s", skipping.', $name, $e->getMessage()));
                }
            }
        } finally {
            $this->webdriver->quit();
            if ($batch) {
                $batch->close();
            }
        }
    }
}
