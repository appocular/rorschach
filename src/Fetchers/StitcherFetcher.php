<?php

namespace Rorschach\Fetchers;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Rorschach\CheckpointFetcher;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Step;
use Rorschach\Stitcher;

class StitcherFetcher implements CheckpointFetcher
{
    protected $webdriver;
    protected $stitcher;

    public function __construct(Config $config, WebDriver $webdriver, Stitcher $stitcher, Output $output)
    {
        $this->config = $config;
        $this->webdriver = $webdriver;
        $this->stitcher = $stitcher;
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Step $step) : string
    {
        $this->output->debug("Loading \"{$step->path}\" in browser.");
        $this->webdriver->get($this->config->getBaseUrl() . $step->path);
        if ($step->hide && $selectors = array_filter(array_values($step->hide))) {
            $this->output->debug(sprintf('Hiding "%s".', implode(',', $selectors)));
            $this->stitcher->hideElements($selectors);
        }

        // Only resize if given sizes. While ConfigFile will make sure these
        // are always set, keeping it optional eases testing.
        if ($step->browserHeight && $step->browserWidth) {
            $this->output->debug("Resizing window to {$step->browserWidth}x{$step->browserHeight}.");
            $this->webdriver->manage()->window()->setSize(new WebDriverDimension($step->browserWidth, $step->browserHeight));
        }

        if ($step->wait) {
            $this->output->debug(sprintf('Waiting %.4fs.', $step->wait * 1000000));
            usleep($step->wait * 1000000);
        }

        $this->output->debug('Stitching screenshot' .
                             ($step->stitchDelay ? sprintf(' with stitch_delay %.4fs', $step->stitchDelay)
                              : '') . '...');
        return $this->stitcher->stitchScreenshot($step->stitchDelay ?? 0);
        $this->output->debug('Done');
    }

    /**
     * End processing.
     */
    public function end() : void
    {
        $this->webdriver->quit();
    }
}
