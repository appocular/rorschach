<?php

declare(strict_types=1);

namespace spec\Rorschach\Fetchers;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverWindow;
use PhpSpec\ObjectBehavior;
use Rorschach\Checkpoint;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Stitcher;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// phpcs:disable Squiz.Scope.MethodScope.Missing
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
class StitcherFetcherSpec extends ObjectBehavior
{
    function let(
        Config $config,
        WebDriver $webdriver,
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
        WebDriver $webdriver,
        Stitcher $stitcher
    ) {
        $webdriver->get('base/path')->shouldBeCalled();
        $webdriver->getCurrentURL()->willReturn('http://example.org/');
        $stitcher->stitchScreenshot(0)->willReturn('image data');

        $this->fetch(new Checkpoint('Test', '/path'))->shouldReturn('image data');
    }

    /**
     * Test that it passes stitchDelay
     */
    function it_passes_stitch_delay(
        WebDriver $webdriver,
        Stitcher $stitcher
    ) {
        $webdriver->get('base/path')->shouldBeCalled();
        $webdriver->getCurrentURL()->willReturn('http://example.org/');
        $stitcher->stitchScreenshot(42)->willReturn('image data');

        $this->fetch(new Checkpoint('Test', '/path', ['stitch_delay' => '42']))->shouldReturn('image data');
    }

    /**
     * Test that it removes elements.
     */
    function it_removes_elements(
        Stitcher $stitcher
    ) {
        $stitcher->stitchScreenshot(0)->willReturn('image data');
        $stitcher->removeElements(['#cookiepopup'])->shouldBeCalled();

        $this->fetch(new Checkpoint('Test', ['path' => '/', 'remove' => ['cookiepopup' => '#cookiepopup']]))
            ->shouldReturn('image data');
    }

    /**
     * Test that it resizes the browser to the given dimensions.
     */
    function it_resizes_browser(
        WebDriver $webdriver,
        Stitcher $stitcher,
        WebDriverOptions $webdriverOptions,
        WebDriverWindow $webdriverWindow
    ) {
        $webdriver->get('base/path')->shouldBeCalled();
        $webdriver->getCurrentURL()->willReturn('http://example.org/');
        $stitcher->stitchScreenshot(0)->willReturn('image data');

        $webdriverWindow->setSize(new WebDriverDimension('800', '600'))->shouldBeCalled();
        $webdriverOptions->window()->willReturn($webdriverWindow);
        $webdriver->manage()->willReturn($webdriverOptions);

        $defaults = ['browser_size' => "800x600"];
        $this->fetch(new Checkpoint('Test', '/path', $defaults))->shouldReturn('image data');
    }

    /**
     * Test that it uses wait_script.
     */
    function it_uses_wait_script(
        Stitcher $stitcher
    ) {
        $stitcher->stitchScreenshot(0)->willReturn('image data');
        $stitcher->waitScript('script body')->shouldBeCalled();

        $this->fetch(new Checkpoint('Test', [
            'path' => '/',
            'wait_script' => 'script body',
        ]))
            ->shouldReturn('image data');
    }

    /**
     * Test that it adds CSS.
     */
    function it_adds_css(
        Stitcher $stitcher
    ) {
        $stitcher->stitchScreenshot(0)->willReturn('image data');
        $stitcher->addCss('some css')->shouldBeCalled();

        $this->fetch(new Checkpoint('Test', ['path' => '/', 'css' => ['hide menu' => 'some css']]))
            ->shouldReturn('image data');
    }
}
