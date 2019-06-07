<?php

namespace spec\Rorschach;

use Facebook\WebDriver\WebDriver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\CheckpointProcessor;
use Rorschach\Config;
use Rorschach\Snapshot;
use Rorschach\Step;
use Rorschach\Stitcher;
use Symfony\Component\Console\Style\StyleInterface;

class SnapshotSpec extends ObjectBehavior
{

    function let(
        Config $config,
        WebDriver $webdriver,
        CheckpointProcessor $processor,
        Stitcher $stitcher,
        StyleInterface $io
    ) {
        $config->getSha()->willReturn('the sha');
        $config->getToken()->willReturn('the token');
        $config->getBrowserHeight()->willReturn(600);
        $config->getBrowserWidth()->willReturn(800);
        $config->getWebdriverUrl()->willReturn('http://webdriver/');
        $config->getBaseUrl()->willReturn('http://baseurl');
        $config->getSteps()->willReturn([new Step('front', '/'), new Step('Page one', '/one')]);
        $config->getHistory()->willReturn(null);

        $this->beConstructedWith($config, $processor, $webdriver, $stitcher, $io);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Snapshot::class);
    }

    /**
     * Tests that it sends a snapshot to processor for each defined step.
     */
    function it_should_run_the_checkpoints(
        Config $config,
        CheckpointProcessor $processor,
        Webdriver $webdriver,
        Stitcher $stitcher,
        StyleInterface $io
    ) {
        $io->error()->shouldNotBeCalled();

        $webdriver->get('http://baseurl/')->shouldBeCalled();
        $stitcher->stitchScreenshot()->willReturn('png data', 'more png data')->shouldBeCalled();
        $processor->process(new Step('front', '/'), 'png data')->shouldBeCalled();
        $webdriver->get('http://baseurl/one')->shouldBeCalled();
        $processor->process(new Step('Page one', '/one'), 'more png data')->shouldBeCalled();

        $processor->end()->shouldBeCalled();
        $webdriver->quit()->shouldBeCalled();

        $this->getWrappedObject()->run();
    }

    /**
     * Test that it skips failed screenshots.
     */
    function it_should_skip_failed_screenshots(
        Config $config,
        CheckpointProcessor $processor,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $config->getSteps()->willReturn([
            new Step('front', '/'),
            new Step('Page one', '/one'),
            new Step('Page two', '/two'),
        ]);

        // We need to get a bit verbose here, as we want the second call to
        // throw an exception.
        $stitcher->stitchScreenshot()->will(function () use ($stitcher) {
            $stitcher->stitchScreenshot()->will(function () use ($stitcher) {
                $stitcher->stitchScreenshot()->willReturn('more png data');
                throw new \RuntimeException('bad stuff');
            });
            return 'png data';
        });

        $webdriver->get('http://baseurl/')->shouldBeCalled();
        $processor->process(new Step('front', '/'), 'png data')->shouldBeCalled();
        $webdriver->get('http://baseurl/one')->shouldBeCalled();
        $processor->process(new Step('Page one', '/one'), Argument::any())->shouldNotBeCalled();
        $webdriver->get('http://baseurl/two')->shouldBeCalled();
        $processor->process(new Step('Page two', '/two'), 'more png data')->shouldBeCalled();

        $processor->end()->shouldBeCalled();
        $webdriver->quit()->shouldBeCalled();

        $this->getWrappedObject()->run();
    }

    /**
     * Test that the hide method hides elements.
     */
    function it_should_hide_specified_elements(
        Config $config,
        CheckpointProcessor $processor,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $config->getSteps()->willReturn([
            new Step('front', ['path' => '/', 'hide' => ['cookiepopup' => '#cookiepopup']]),
            // Null values should be ignored.
            new Step('Page two', ['path' => '/two', 'hide' => ['cookiepopup' => null]]),
        ]);

        $stitcher->hideElements(['#cookiepopup'])->shouldBeCalled();

        $this->getWrappedObject()->run();
    }
}
