<?php

namespace spec\Rorschach\Fetchers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Facebook\WebDriver\WebDriver;
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
        $stitcher->stitchScreenshot()->willReturn('image data');
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $this->fetch(new Step('Test', '/path'))->shouldReturn('image data');
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
        $stitcher->stitchScreenshot()->willReturn('image data');
        $stitcher->hideElements(['#cookiepopup'])->shouldBeCalled();
        $this->beConstructedWith($config, $webdriver, $stitcher);

        $this->fetch(new Step('Test', ['path' => '/', 'hide' => ['cookiepopup' => '#cookiepopup']]))
            ->shouldReturn('image data');
    }
}
