<?php

namespace Rorschach\Fetchers;

use Facebook\WebDriver\WebDriver;
use Rorschach\CheckpointFetcher;
use Rorschach\Config;
use Rorschach\Step;
use Rorschach\Stitcher;

class StitcherFetcher implements CheckpointFetcher
{
    protected $webdriver;
    protected $stitcher;

    public function __construct(Config $config, WebDriver $webdriver, Stitcher $stitcher)
    {
        $this->config = $config;
        $this->webdriver = $webdriver;
        $this->stitcher = $stitcher;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Step $step) : string
    {
        $this->webdriver->get($this->config->getBaseUrl() . $step->path);
        if ($step->hide && $selectors = array_filter(array_values($step->hide))) {
            $this->stitcher->hideElements($selectors);
        }

        return $this->stitcher->stitchScreenshot();
    }

    /**
     * End processing.
     */
    public function end() : void
    {
        $this->webdriver->quit();
    }
}
