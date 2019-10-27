<?php

namespace spec\Rorschach\Fetchers;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverWindow;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Checkpoint;
use Rorschach\Stitcher;

class StitcherFetcherSpec extends ObjectBehavior
{
    function let(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher,
        Output $output
    ) {
        $config->getBaseUrl()->willReturn('base');

        $this->beConstructedWith($config, $webdriver, $stitcher, $output);
    }

    /**
     * Test that it writes out images.
     */
    function it_fetches_screenshot(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $webdriver->get('base/path')->shouldBeCalled();
        $webdriver->getCurrentURL()->willReturn('http://example.org/');
        $stitcher->stitchScreenshot(0, false)->willReturn('image data');

        $this->fetch(new Checkpoint('Test', '/path'))->shouldReturn('image data');
    }

    /**
     * Test that it passes stitchDelay
     */
    function it_passes_stitch_delay(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $webdriver->get('base/path')->shouldBeCalled();
        $webdriver->getCurrentURL()->willReturn('http://example.org/');
        $stitcher->stitchScreenshot(42, false)->willReturn('image data');

        $this->fetch(new Checkpoint('Test', '/path', ['stitch_delay' => 42]))->shouldReturn('image data');
    }

    /**
     * Test that it hides elements.
     */
    function it_hides_elements(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $stitcher->stitchScreenshot(0, false)->willReturn('image data');
        $stitcher->hideElements(['#cookiepopup'])->shouldBeCalled();

        $this->fetch(new Checkpoint('Test', ['path' => '/', 'hide' => ['cookiepopup' => '#cookiepopup']]))
            ->shouldReturn('image data');
    }

    /**
     * Test that it removes elements.
     */
    function it_removes_elements(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $stitcher->stitchScreenshot(0, false)->willReturn('image data');
        $stitcher->removeElements(['#cookiepopup'])->shouldBeCalled();

        $this->fetch(new Checkpoint('Test', ['path' => '/', 'remove' => ['cookiepopup' => '#cookiepopup']]))
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
        $webdriver->get('base/path')->shouldBeCalled();
        $webdriver->getCurrentURL()->willReturn('http://example.org/');
        $stitcher->stitchScreenshot(0, false)->willReturn('image data');

        $webdriverWindow->setSize(new WebDriverDimension('800', '600'))->shouldBeCalled();
        $webdriverOptions->window()->willReturn($webdriverWindow);
        $webdriver->manage()->willReturn($webdriverOptions);

        $defaults = ['browser_height' => 600, 'browser_width' => 800];
        $this->fetch(new Checkpoint('Test', '/path', $defaults))->shouldReturn('image data');
    }

    /**
     * Test that it uses wait_script.
     */
    function it_uses_wait_script(
        Config $config,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $stitcher->stitchScreenshot(0, false)->willReturn('image data');
        $stitcher->waitScript('script body')->willReturn(false, false, true)->shouldBeCalled();

        $this->fetch(new Checkpoint('Test', [
            'path' => '/',
            'wait_script' => 'script body',
        ]))
            ->shouldReturn('image data');
    }

    /**
     * Test that it allows for overriding the animation killing.
     */
    function it_disables_anim_killing(Stitcher $stitcher)
    {
        $stitcher->stitchScreenshot(0, true)->willReturn('image data')->shouldBeCalled();
        $this->fetch(new Checkpoint('Test', [
            'path' => '/',
            'dont_kill_animations' => true,
        ]))
            ->shouldReturn('image data');
    }
}
