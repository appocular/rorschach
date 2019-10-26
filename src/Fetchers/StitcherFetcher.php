<?php

namespace Rorschach\Fetchers;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Rorschach\CheckpointFetcher;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Checkpoint;
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
    public function fetch(Checkpoint $checkpoint) : string
    {
        // Only resize if given sizes. While ConfigFile will make sure these
        // are always set, keeping it optional eases testing.
        if ($checkpoint->browserHeight && $checkpoint->browserWidth) {
            $this->output->debug("Resizing window to {$checkpoint->browserWidth}❌{$checkpoint->browserHeight}.");
            $this->webdriver->manage()->window()->setSize(new WebDriverDimension((int) $checkpoint->browserWidth, (int) $checkpoint->browserHeight));
        }

        $this->output->debug("Loading \"{$checkpoint->path}\" in browser.");
        $this->webdriver->get($this->config->getBaseUrl() . $checkpoint->path);

        if ($checkpoint->wait) {
            $this->output->debug(sprintf('Waiting %.4fs.', $checkpoint->wait));
            usleep($checkpoint->wait * 1000000);
        }

        if ($checkpoint->waitScript) {
            $this->output->debug('Waiting for wait_script');
            $this->stitcher->waitScript($checkpoint->waitScript);
        }

        if ($checkpoint->hide && $selectors = array_filter(array_values($checkpoint->hide))) {
            $this->output->debug(sprintf('Hiding "%s".', implode(',', $selectors)));
            $this->stitcher->hideElements($selectors);
        }

        $this->output->debug(sprintf(
            'Stitching "%s"%s...',
            $this->webdriver->getCurrentURL(),
            ($checkpoint->stitchDelay ? sprintf(' with stitch_delay %.4fs', $checkpoint->stitchDelay) : '')
        ));
        return $this->stitcher->stitchScreenshot($checkpoint->stitchDelay ?? 0, (bool) $checkpoint->dontKillAnimations);
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
