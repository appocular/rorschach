<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Checkpoint;
use Rorschach\CheckpointProcessor;
use Rorschach\Config;
use Rorschach\Helpers\Output;
use Rorschach\Helpers\WorkerFactory;
use Rorschach\Helpers\WorkerProcess;
use Rorschach\Multiplexer;
use Rorschach\Variation;

class MultiplexerSpec extends ObjectBehavior
{
    function let(
        Config $config,
        Output $output,
        WorkerFactory $workerFactory,
        WorkerProcess $workerProcess,
        CheckpointProcessor $processor
    ) {
        $this->beConstructedWith($config, $output, $workerFactory, $processor);
    }

    function it_slices_checkpoints_up(
        Config $config,
        WorkerFactory $workerFactory,
        WorkerProcess $workerProcess
    ) {
        $config->getWorkers()->willReturn(4);
        $config->getVariants()->willReturn([]);
        $config->getCheckpoints()->willReturn([1, 2, 3, 4, 5, 6, 7, 8, 9, 0]);

        $workerProcess->isRunning()->willReturn(false);
        $workerProcess->getIncrementalOutput()->willReturn(null);
        $workerProcess->getIncrementalErrorOutput()->willReturn(null);
        $workerProcess->getExitCode()->willReturn(0);

        $workerFactory->create([1, 5, 9])->willReturn($workerProcess)->shouldBeCalled();
        $workerFactory->create([2, 6, 0])->willReturn($workerProcess)->shouldBeCalled();
        $workerFactory->create([3, 7])->willReturn($workerProcess)->shouldBeCalled();
        $workerFactory->create([4, 8])->willReturn($workerProcess)->shouldBeCalled();

        $this->run()->shouldReturn(true);
    }

    function it_collects_checkpoint_variations(
        Config $config,
        Variation $variation,
        WorkerFactory $workerFactory,
        WorkerProcess $workerProcess
    ) {
        $variation->getVariations([1, 2, 3])->willReturn([4, 5, 6])->shouldBeCalled();
        $config->getWorkers()->willReturn(1);
        $config->getVariants()->willReturn([$variation]);
        $config->getCheckpoints()->willReturn([1, 2, 3]);

        $workerProcess->isRunning()->willReturn(false);
        $workerProcess->getIncrementalOutput()->willReturn(null);
        $workerProcess->getIncrementalErrorOutput()->willReturn(null);
        $workerProcess->getExitCode()->willReturn(0);

        $workerFactory->create([4, 5, 6])->willReturn($workerProcess)->shouldBeCalled();

        $this->run()->shouldReturn(true);
    }

    function it_only_start_needed_number_of_workers(
        Config $config,
        WorkerFactory $workerFactory,
        WorkerProcess $workerProcess
    ) {
        $config->getWorkers()->willReturn(16);
        $config->getVariants()->willReturn([]);
        $config->getCheckpoints()->willReturn([1, 2, 3]);

        $workerProcess->isRunning()->willReturn(false);
        $workerProcess->getIncrementalOutput()->willReturn(null);
        $workerProcess->getIncrementalErrorOutput()->willReturn(null);
        $workerProcess->getExitCode()->willReturn(0);

        $workerFactory->create([1])->willReturn($workerProcess)->shouldBeCalled();
        $workerFactory->create([2])->willReturn($workerProcess)->shouldBeCalled();
        $workerFactory->create([3])->willReturn($workerProcess)->shouldBeCalled();

        $this->run()->shouldReturn(true);
    }

    function it_should_print_worker_output(
        Config $config,
        Output $output,
        WorkerFactory $workerFactory,
        WorkerProcess $workerProcess
    ) {
        $config->getWorkers()->willReturn(16);
        $config->getVariants()->willReturn([]);
        $config->getCheckpoints()->willReturn([1]);

        $workerProcess->isRunning()->willReturn(false);
        $workerProcess->getIncrementalOutput()->willReturn('banana');
        $workerProcess->getIncrementalErrorOutput()->willReturn(null);
        $workerProcess->getExitCode()->willReturn(0);

        $workerFactory->create([1])->willReturn($workerProcess);

        $output->info(Argument::any())->willReturn();
        $output->numberedLine(1, 'banana')->shouldBeCalled();

        $this->run()->shouldReturn(true);
    }

    function it_should_print_worker_error_output(
        Config $config,
        Output $output,
        WorkerFactory $workerFactory,
        WorkerProcess $workerProcess
    ) {
        $config->getWorkers()->willReturn(16);
        $config->getVariants()->willReturn([]);
        $config->getCheckpoints()->willReturn([1]);

        $workerProcess->isRunning()->willReturn(false);
        $workerProcess->getIncrementalOutput()->willReturn(null);
        $workerProcess->getIncrementalErrorOutput()->willReturn('the error');
        $workerProcess->getExitCode()->willReturn(0);

        $workerFactory->create([1])->willReturn($workerProcess);

        $output->info(Argument::any())->willReturn();
        $output->error('Worker 1 error, output:')->shouldBeCalled();
        $output->numberedLine(1, 'the error')->shouldBeCalled();

        $this->run()->shouldReturn(true);
    }

    function it_should_return_false_if_worker_exits_with_error(
        Config $config,
        Output $output,
        WorkerFactory $workerFactory,
        WorkerProcess $workerProcess
    ) {
        $config->getWorkers()->willReturn(16);
        $config->getVariants()->willReturn([]);
        $config->getCheckpoints()->willReturn([1]);

        $workerProcess->isRunning()->willReturn(false);
        $workerProcess->getIncrementalOutput()->willReturn(null);
        $workerProcess->getIncrementalErrorOutput()->willReturn('the error');
        $workerProcess->getExitCode()->willReturn(1);

        $workerFactory->create([1])->willReturn($workerProcess);

        $output->info(Argument::any())->willReturn();
        $output->error('Worker 1 error, output:')->shouldBeCalled();
        $output->numberedLine(1, 'the error')->shouldBeCalled();

        $this->run()->shouldReturn(false);
    }
}
