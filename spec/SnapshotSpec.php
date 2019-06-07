<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\CheckpointProcessor;
use Rorschach\CheckpointFetcher;
use Rorschach\Config;
use Rorschach\Snapshot;
use Rorschach\Step;
use Symfony\Component\Console\Style\StyleInterface;

class SnapshotSpec extends ObjectBehavior
{

    function let(
        Config $config,
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
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
        $config->getVerbose()->willReturn(false);

        $this->beConstructedWith($config, $fetcher, $processor, $io);
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
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        StyleInterface $io
    ) {
        $io->error()->shouldNotBeCalled();

        $step = new Step('front', '/');
        $fetcher->fetch($step)->willReturn('png data')->shouldBeCalled();
        $processor->process($step, 'png data')->shouldBeCalled();
        $step = new Step('Page one', '/one');
        $fetcher->fetch($step)->willReturn('more png data')->shouldBeCalled();
        $processor->process($step, 'more png data')->shouldBeCalled();

        $fetcher->end()->shouldBeCalled();
        $processor->end()->shouldBeCalled();

        $this->getWrappedObject()->run();
    }

    /**
     * Test that it skips failed screenshots.
     */
    function it_should_skip_failed_screenshots(
        Config $config,
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor
    ) {
        $steps = [
            new Step('front', '/'),
            new Step('Page one', '/one'),
            new Step('Page two', '/two'),
        ];
        $config->getSteps()->willReturn($steps);

        $fetcher->fetch($steps[0])->willReturn('png data')->shouldBeCalled();
        $processor->process($steps[0], 'png data')->shouldBeCalled();
        $fetcher->fetch($steps[1])->willThrow(new \RuntimeException('bad stuff'))->shouldBeCalled();
        $processor->process($steps[1], Argument::any())->shouldNotBeCalled();
        $fetcher->fetch($steps[2])->willReturn('more png data')->shouldBeCalled();
        $processor->process($steps[2], 'more png data')->shouldBeCalled();

        $fetcher->end()->shouldBeCalled();
        $processor->end()->shouldBeCalled();

        $this->getWrappedObject()->run();
    }

    /**
     * It should be verbose when asked to.
     */
    function it_should_be_verbose(
        Config $config,
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        StyleInterface $io
    ) {
        $steps = [
            new Step('front', '/'),
            new Step('Page two', '/two'),
        ];
        $config->getSteps()->willReturn($steps);
        $config->getVerbose()->willReturn(true);

        $io->text('Checkpointing "front"...')->shouldBeCalled();
        $io->text('Checkpointing "Page two"...')->shouldBeCalled();

        $fetcher->fetch($steps[0])->willReturn('png data')->shouldBeCalled();
        $processor->process($steps[0], 'png data')->shouldBeCalled();
        $fetcher->fetch($steps[1])->willReturn('more png data')->shouldBeCalled();
        $processor->process($steps[1], 'more png data')->shouldBeCalled();

        $fetcher->end()->shouldBeCalled();
        $processor->end()->shouldBeCalled();

        $this->getWrappedObject()->run();
    }
}
