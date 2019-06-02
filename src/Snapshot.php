<?php

namespace Rorschach;

use Facebook\WebDriver\WebDriver;

class Snapshot
{
    protected $config;
    protected $appocular;
    protected $webdriver;

    public function __construct(Config $config, Appocular $appocular, WebDriver $webdriver)
    {
        $this->config = $config;
        $this->appocular = $appocular;
        $this->webdriver = $webdriver;
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
                $batch->checkpoint($name, $this->webdriver->takeScreenshot());
            }
        } finally {
            $this->webdriver->quit();
            if ($batch) {
                $batch->close();
            }
        }
    }
}
