<?php

declare(strict_types=1);

namespace spec\Rorschach\Processors;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular;
use Rorschach\Appocular\Batch;
use Rorschach\Checkpoint;
use Rorschach\Config;
use Rorschach\Helpers\Output;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// phpcs:disable Squiz.Scope.MethodScope.Missing
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
class AppocularProcessorSpec extends ObjectBehavior
{
    function it_should_not_start_batch_on_construction(
        Config $config,
        Appocular $appocular,
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

        $batch->checkpoint(Argument::any(), Argument::any(), Argument::any())->willReturn(true);
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

        $batch->checkpoint(Argument::any(), Argument::any(), Argument::any())->willReturn(true);
        $appocular->startBatch('the sha', "the\nhistory")->willReturn($batch)->shouldBeCalled();

        $this->beConstructedWith($config, $appocular, $output);
        $this->process(new Checkpoint('test', 'test'), 'data');
    }

    /**
     * Test that it passes data when checkpointing.
     */
    function it_should_pass_data_when_checkpointing(
        Config $config,
        Appocular $appocular,
        Batch $batch,
        Output $output
    ) {
        $batch->checkpoint('test', 'data', ['some' => 'data'])->shouldBeCalled();
        $config->getSha()->willReturn('the sha');
        $config->getHistory()->willReturn(null);

        $appocular->startBatch('the sha', null)->willReturn($batch)->shouldBeCalled();

        $this->beConstructedWith($config, $appocular, $output);
        $this->process(new Checkpoint('test', 'test', ['meta' => ['some' => 'data']]), 'data');
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

        $output->newLine()->shouldBeCalled();
        $output->message('Verify snapshot at https://test.appocular.io/the sha')->shouldBeCalled();

        $this->beConstructedWith($config, $appocular, $output);
        $this->summarize();
    }
}
