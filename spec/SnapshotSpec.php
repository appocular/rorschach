<?php

namespace spec\Rorschach;

use Facebook\WebDriver\WebDriver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular;
use Rorschach\Appocular\Batch;
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
        Appocular $appocular,
        Batch $batch,
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

        $appocular->startBatch(Argument::any(), Argument::any())->willReturn($batch);

        $batch->checkpoint(Argument::any(), Argument::any())->willReturn(true);

        $batch->close()->willReturn(true);

        $this->beConstructedWith($config, $appocular, $webdriver, $stitcher, $io);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Snapshot::class);
    }

    function it_should_run_the_validations(
        Config $config,
        Appocular $appocular,
        Batch $batch,
        Webdriver $webdriver,
        Stitcher $stitcher,
        StyleInterface $io
    ) {
        $appocular->startBatch()->willReturn($batch);
        $io->error()->shouldNotBeCalled();

        $webdriver->get('http://baseurl/')->shouldBeCalled();
        $stitcher->stitchScreenshot()->willReturn('png data', 'more png data')->shouldBeCalled();
        $batch->checkpoint('front', 'png data')->shouldBeCalled();
        $webdriver->get('http://baseurl/one')->shouldBeCalled();
        $batch->checkpoint('Page one', 'more png data')->shouldBeCalled();

        $batch->close()->willReturn(true);
        $webdriver->quit()->shouldBeCalled();

        $this->getWrappedObject()->run();
    }

    function it_should_pass_the_history(Config $config, Appocular $appocular, Batch $batch, Webdriver $webdriver)
    {
        $history = "the\nhistroy";
        $config->getHistory()->willReturn($history);
        // No steps, to avoid calls on $webdriver.
        $config->getSteps()->willReturn([]);
        $appocular->startBatch('the sha', $history)->shouldBeCalled()->willReturn($batch);
        $batch->close()->willReturn(true);

        $this->getWrappedObject()->run();
    }

    function it_should_skip_failed_screenshots(
        Config $config,
        Appocular $appocular,
        Batch $batch,
        Webdriver $webdriver,
        Stitcher $stitcher
    ) {
        $config->getSteps()->willReturn([
            new Step('front', '/'),
            new Step('Page one', '/one'),
            new Step('Page two', '/two'),
        ]);
        $appocular->startBatch()->willReturn($batch);


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
        $batch->checkpoint('front', 'png data')->shouldBeCalled();
        $webdriver->get('http://baseurl/one')->shouldBeCalled();
        $batch->checkpoint('Page one', Argument::any())->shouldNotBeCalled();
        $webdriver->get('http://baseurl/two')->shouldBeCalled();
        $batch->checkpoint('Page two', 'more png data')->shouldBeCalled();

        $batch->close()->willReturn(true);
        $webdriver->quit()->shouldBeCalled();

        $this->getWrappedObject()->run();
    }
}
