<?php

namespace spec\Rorschach\Processors;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular;
use Rorschach\Appocular\Batch;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Checkpoint;

class AppocularProcessorSpec extends ObjectBehavior
{
    function it_should_not_start_batch_on_construction(
        Config $config,
        Appocular $appocular,
        Batch $batch,
        Output $output
    ) {
        $config->getSha()->willReturn('the sha');
        $config->getHistory()->willReturn(null);

        $appocular->startBatch()->shouldNotBeCalled();

        $this->beConstructedWith($config, $appocular, $output);
    }

    function it_should_start_batch_on_first_checkpoint(
        Config $config,
        Appocular $appocular,
        Batch $batch,
        Output $output
    ) {
        $config->getSha()->willReturn('the sha');
        $config->getHistory()->willReturn(null);

        $appocular->startBatch('the sha', null)->willReturn($batch)->shouldBeCalled();

        $this->beConstructedWith($config, $appocular, $output);
        $this->process(new Checkpoint('test', 'test'), 'data');
    }

    /**
     * Tests that it passes the history when creating the batch.
     */
    function it_should_pass_the_history(
        Config $config,
        Appocular $appocular,
        Batch $batch,
        Output $output
    ) {
        $config->getSha()->willReturn('the sha');
        $config->getHistory()->willReturn("the\nhistory");

        $appocular->startBatch('the sha', "the\nhistory")->willReturn($batch)->shouldBeCalled();

        $this->beConstructedWith($config, $appocular, $output);
        $this->process(new Checkpoint('test', 'test'), 'data');
    }

    /**
     * Test that it prints the URL to Appocular at the end.
     */
    function it_should_print_link(
        Config $config,
        Appocular $appocular,
        Batch $batch,
        Output $output
    ) {
        $config->getBase()->willReturn('test.appocular.io');
        $config->getSha()->willReturn('the sha');
        $config->getHistory()->willReturn("");

        $appocular->startBatch('the sha', "")->willReturn($batch);

        $output->debug(Argument::any())->willReturn();
        $output->newLine()->shouldBeCalled();
        $output->message('Verify snapshot at https://test.appocular.io/the sha')->shouldBeCalled();

        $this->beConstructedWith($config, $appocular, $output);
        $this->summarize();
    }
}
