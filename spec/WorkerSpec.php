<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\CheckpointFetcher;
use Rorschach\CheckpointProcessor;
use Rorschach\Helpers\Output;
use Rorschach\Worker;
use Rorschach\Checkpoint;

class WorkerSpec extends ObjectBehavior
{

    function let(
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output
    ) {
        $output->debug(Argument::any())->willReturn();
        $output->info(Argument::any())->willReturn();
    }

    function it_is_initializable(
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output
    ) {
        $checkpoints = [
            new Checkpoint('front', '/'),
        ];
        $this->beConstructedWith($fetcher, $processor, $output, \serialize($checkpoints));
        $this->shouldHaveType(Worker::class);
    }

    function it_should_print_error_if_missing_checkpoints(
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output
    ) {
        $checkpoints = [];
        $output->error(Argument::any())->shouldBeCalled();
        $this->beConstructedWith($fetcher, $processor, $output, \serialize($checkpoints));
        $this->run()->shouldReturn(false);
    }

    /**
     * Tests that it sends a snapshot to processor for each defined checkpoint.
     */
    function it_should_run_the_checkpoints(
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output
    ) {
        $output->error()->shouldNotBeCalled();
        $output->message(Argument::any())->willReturn();

        $checkpoints = [
            new Checkpoint('front', '/'),
            new Checkpoint('Page one', '/one'),
        ];
        $fetcher->fetch($checkpoints[0])->willReturn('png data')->shouldBeCalled();
        $processor->process($checkpoints[0], 'png data')->shouldBeCalled();
        $fetcher->fetch($checkpoints[1])->willReturn('more png data')->shouldBeCalled();
        $processor->process($checkpoints[1], 'more png data')->shouldBeCalled();

        $fetcher->end()->shouldBeCalled();
        $processor->end()->shouldBeCalled();

        $this->beConstructedWith($fetcher, $processor, $output, \serialize($checkpoints));

        $this->run()->shouldReturn(true);
    }

    /**
     * Test that it skips failed screenshots.
     */
    function it_should_skip_failed_screenshots(
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output
    ) {
        $checkpoints = [
            new Checkpoint('front', '/'),
            new Checkpoint('Page one', '/one'),
            new Checkpoint('Page two', '/two'),
        ];

        $output->error(Argument::any())->willReturn();
        $output->message(Argument::any())->willReturn();

        $fetcher->fetch($checkpoints[0])->willReturn('png data')->shouldBeCalled();
        $processor->process($checkpoints[0], 'png data')->shouldBeCalled();
        $fetcher->fetch($checkpoints[1])->willThrow(new \RuntimeException('bad stuff'))->shouldBeCalled();
        $processor->process($checkpoints[1], Argument::any())->shouldNotBeCalled();
        $fetcher->fetch($checkpoints[2])->willReturn('more png data')->shouldBeCalled();
        $processor->process($checkpoints[2], 'more png data')->shouldBeCalled();

        $fetcher->end()->shouldBeCalled();
        $processor->end()->shouldBeCalled();

        $this->beConstructedWith($fetcher, $processor, $output, \serialize($checkpoints));

        $this->run()->shouldReturn(false);
    }

    /**
     * It should print progress.
     */
    function it_should_print_progress(
        CheckpointFetcher $fetcher,
        CheckpointProcessor $processor,
        Output $output
    ) {
        $checkpoints = [
            new Checkpoint('front', '/'),
            new Checkpoint('Page two', '/two'),
        ];

        $output->message('Checkpointing "front"...')->shouldBeCalled();
        $output->message('Checkpointing "Page two"...')->shouldBeCalled();

        $fetcher->fetch($checkpoints[0])->willReturn('png data')->shouldBeCalled();
        $processor->process($checkpoints[0], 'png data')->shouldBeCalled();
        $fetcher->fetch($checkpoints[1])->willReturn('more png data')->shouldBeCalled();
        $processor->process($checkpoints[1], 'more png data')->shouldBeCalled();

        $fetcher->end()->shouldBeCalled();
        $processor->end()->shouldBeCalled();

        $this->beConstructedWith($fetcher, $processor, $output, \serialize($checkpoints));

        $this->run()->shouldReturn(true);
    }
}
