<?php

namespace spec\Rorschach\Processors;

use PhpSpec\ObjectBehavior;
use Rorschach\Config;
use Rorschach\Step;
use Rorschach\Appocular;
use Rorschach\Appocular\Batch;

class AppocularProcessorSpec extends ObjectBehavior
{
    function it_should_start_batch_on_construction(
        Config $config,
        Appocular $appocular,
        Batch $batch
    ) {
        $config->getSha()->willReturn('the sha');
        $config->getHistory()->willReturn(null);

        $appocular->startBatch('the sha', null)->willReturn($batch)->shouldBeCalled();

        $this->beConstructedWith($config, $appocular);
        $this->process(new Step('test', 'test'), 'data');
    }

    /**
     * Tests that it passes the history when creating the batch.
     */
    function it_should_pass_the_history(
        Config $config,
        Appocular $appocular,
        Batch $batch
    ) {
        $config->getSha()->willReturn('the sha');
        $config->getHistory()->willReturn("the\nhistory");

        $appocular->startBatch('the sha', "the\nhistory")->willReturn($batch)->shouldBeCalled();

        $this->beConstructedWith($config, $appocular);
        $this->process(new Step('test', 'test'), 'data');
    }
}
