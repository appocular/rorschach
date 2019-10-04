<?php

namespace Rorschach\Fetchers;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
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

        // Only resize if given sizes. While ConfigFile will make sure these
        // are always set, keeping it optional eases testing.
        if ($step->browserHeight && $step->browserWidth) {
            $this->webdriver->manage()->window()->setSize(new WebDriverDimension($step->browserWidth, $step->browserHeight));
        }

        if ($step->wait) {
            usleep($step->wait * 1000000);
        }

        return $this->stitcher->stitchScreenshot($step->stitchDelay ?? 0);
    }

    /**
     * End processing.
     */
    public function end() : void
    {
        $this->webdriver->quit();
    }
}
