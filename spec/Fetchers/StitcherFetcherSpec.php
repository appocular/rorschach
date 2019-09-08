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
     * Helper to set up browser resize expections.
     */
    function expectBrowserSize($width, $height)
    {
        $webdriverWindow->setSize(new WebDriverDimension($width, $height))->shouldBeCalled();
        $webdriverOptions->window()->willReturn($webdriverWindow);
        $webdriver->manage()->willReturn($webdriverOptions);
    }

    /**
     * Test that it writes out images.
     */
    function it_fetches_screenshot(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher,
        WebDriverOptions $webdriverOptions,
        WebDriverWindow $webdriverWindow
    ) {
        $config->getBaseUrl()->willReturn('base');
        $config->getBrowserWidth()->willReturn(800);
        $config->getBrowserHeight()->willReturn(600);
        $webdriver->get('base/path')->shouldBeCalled();
        $stitcher->stitchScreenshot()->willReturn('image data');
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $webdriverWindow->setSize(new WebDriverDimension('800', '600'))->shouldBeCalled();
        $webdriverOptions->window()->willReturn($webdriverWindow);
        $webdriver->manage()->willReturn($webdriverOptions);

        $this->fetch(new Step('Test', '/path'))->shouldReturn('image data');
    }

    /**
     * Test that it hides elements.
     */
    function it_hides_elements(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher,
        WebDriverOptions $webdriverOptions,
        WebDriverWindow $webdriverWindow
    ) {
        $config->getBaseUrl()->willReturn('base');
        $config->getBrowserWidth()->willReturn(800);
        $config->getBrowserHeight()->willReturn(600);
        $stitcher->stitchScreenshot()->willReturn('image data');
        $stitcher->hideElements(['#cookiepopup'])->shouldBeCalled();
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $webdriver->get('base/')->shouldBeCalled();
        $webdriverWindow->setSize(new WebDriverDimension('800', '600'))->shouldBeCalled();
        $webdriverOptions->window()->willReturn($webdriverWindow);
        $webdriver->manage()->willReturn($webdriverOptions);

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
        $config->getBrowserWidth()->willReturn(800);
        $config->getBrowserHeight()->willReturn(600);
        $webdriver->get('base/path')->shouldBeCalled();
        $stitcher->stitchScreenshot()->willReturn('image data');
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $webdriverWindow->setSize(new WebDriverDimension('800', '600'))->shouldBeCalled();
        $webdriverOptions->window()->willReturn($webdriverWindow);
        $webdriver->manage()->willReturn($webdriverOptions);

        $this->fetch(new Step('Test', '/path'))->shouldReturn('image data');

    }
}
