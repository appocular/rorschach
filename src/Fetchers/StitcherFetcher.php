<?php

declare(strict_types=1);

namespace Rorschach\Fetchers;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Rorschach\Checkpoint;
use Rorschach\CheckpointFetcher;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Stitcher;

class StitcherFetcher implements CheckpointFetcher
{
    /**
     * Webdriver to use.
     *
     * @var \Facebook\WebDriver\WebDriver
     */
    protected $webdriver;

    /**
     * Stitcher for stitching screenshots.
     *
     * @var \Rorschach\Stitcher
     */
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
    public function fetch(Checkpoint $checkpoint): string
    {
        $size = $checkpoint->browserSize;

        // Only resize if given sizes. While ConfigFile will make sure these
        // are always set, keeping it optional eases testing.
        if ($size) {
            $this->output->debug("Resizing window to {$size->width}❌{$size->height}.");
            $this->webdriver->manage()->window()->setSize(new WebDriverDimension($size->width, $size->height));
        }

        $this->output->debug("Loading \"{$checkpoint->path}\" in browser.");
        $this->webdriver->get($this->config->getBaseUrl() . $checkpoint->path);

        if ($checkpoint->wait) {
            $this->output->debug(\sprintf('Waiting %.4fs.', $checkpoint->wait));
            \usleep($checkpoint->wait * 1000000);
        }

        if ($checkpoint->waitScript) {
            $this->output->debug('Waiting for wait_script');
            $this->stitcher->waitScript($checkpoint->waitScript);
        }

        $selectors = $checkpoint->remove ? \array_filter(\array_values($checkpoint->remove)) : [];

        if ($selectors) {
            $this->output->debug(\sprintf('Removing "%s".', \implode(',', $selectors)));
            $this->stitcher->removeElements($selectors);
        }

        $selectors = $checkpoint->css ? \array_filter(\array_values($checkpoint->css)) : [];

        if ($selectors) {
            foreach ($checkpoint->css as $name => $css) {
                $this->output->debug(\sprintf('Adding "%s" CSS.', $name));
                $this->stitcher->addCss($css);
            }
        }

        $this->output->debug(\sprintf(
            'Stitching "%s"%s...',
            $this->webdriver->getCurrentURL(),
            ($checkpoint->stitchDelay ? \sprintf(' with stitch_delay %.4fs', $checkpoint->stitchDelay) : ''),
        ));

        $result = $this->stitcher->stitchScreenshot($checkpoint->stitchDelay ?? 0);
        $this->output->debug('Done');

        return $result;
    }

    /**
     * End processing.
     */
    public function end(): void
    {
        $this->webdriver->quit();
    }
}
