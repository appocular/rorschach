<?php

namespace spec\Rorschach\Fetchers;

use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverWindow;
use Facebook\WebDriver\WebDriver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Config;
use Rorschach\Step;
use Rorschach\Stitcher;

class StitcherFetcherSpec extends ObjectBehavior
{
    /**
     * Test that it writes out images.
     */
    function it_fetches_screenshot(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $config->getBaseUrl()->willReturn('base');
        $webdriver->get('base/path')->shouldBeCalled();
        $stitcher->stitchScreenshot(0)->willReturn('image data');
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $this->fetch(new Step('Test', '/path'))->shouldReturn('image data');
    }

    /**
     * Test that it passes stitchDelay
     */
    function it_passes_stitch_delay(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $config->getBaseUrl()->willReturn('base');
        $webdriver->get('base/path')->shouldBeCalled();
        $stitcher->stitchScreenshot(42)->willReturn('image data');
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $this->fetch(new Step('Test', '/path', ['stitch_delay' => 42]))->shouldReturn('image data');
    }

    /**
     * Test that it hides elements.
     */
    function it_hides_elements(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $config->getBaseUrl()->willReturn('base');
        $stitcher->stitchScreenshot(0)->willReturn('image data');
        $stitcher->hideElements(['#cookiepopup'])->shouldBeCalled();
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $this->fetch(new Step('Test', ['path' => '/', 'hide' => ['cookiepopup' => '#cookiepopup']]))
            ->shouldReturn('image data');
    }

    /**
     * Test that it resizes the browser to the given dimensions.
     */
    function it_resizes_browser(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher,
        WebDriverOptions $webdriverOptions,
        WebDriverWindow $webdriverWindow
    ) {
        $config->getBaseUrl()->willReturn('base');
        $webdriver->get('base/path')->shouldBeCalled();
        $stitcher->stitchScreenshot(0)->willReturn('image data');
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $webdriverWindow->setSize(new WebDriverDimension('800', '600'))->shouldBeCalled();
        $webdriverOptions->window()->willReturn($webdriverWindow);
        $webdriver->manage()->willReturn($webdriverOptions);

        $defaults = ['browser_height' => 600, 'browser_width' => 800];
        $this->fetch(new Step('Test', '/path', $defaults))->shouldReturn('image data');

    }
}
