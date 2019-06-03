<?php

namespace Rorschach;

use Facebook\WebDriver\WebDriver;

class Snapshot
{
    protected $config;
    protected $appocular;
    protected $webdriver;
    protected $stitcher;

    public function __construct(Config $config, Appocular $appocular, WebDriver $webdriver, Stitcher $stitcher)
    {
        $this->config = $config;
        $this->appocular = $appocular;
        $this->webdriver = $webdriver;
        $this->stitcher = $stitcher;
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
                $this->webdriver->get($this->config->getBaseUrl() . $path);
                $batch->checkpoint($name, $this->stitcher->stitchScreenshot());
            }
        } finally {
            $this->webdriver->quit();
            if ($batch) {
                $batch->close();
            }
        }
    }
}
