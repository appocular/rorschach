<?php

namespace spec\Rorschach\Helpers;

use Carbon\Carbon;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Helpers\Output;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OutputSpec extends ObjectBehavior
{

    function let(OutputInterface $output, OutputFormatter $formatter, ConsoleOutputInterface $stdOutput)
    {
        $output->getFormatter()->willReturn($formatter);
        $output->isDebug()->willReturn(false);
        $stdOutput->getFormatter()->willReturn($formatter);
        $stdOutput->isDebug()->willReturn(false);
        $this->beConstructedWith($output);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Output::class);
    }

    function it_outputs_messages(OutputInterface $output)
    {
        $output->writeln('<message>test</>', OutputInterface::VERBOSITY_NORMAL)->shouldBeCalled();
        $this->message('test');
    }

    function it_outputs_info_messages(OutputInterface $output)
    {
        $output->writeln('<info>test</>', OutputInterface::VERBOSITY_VERBOSE)->shouldBeCalled();
        $this->info('test');
    }

    function it_outputs_debug_messages(OutputInterface $output)
    {
        $output->writeln('<debug>test</>', OutputInterface::VERBOSITY_DEBUG)->shouldBeCalled();
        $this->debug('test');
    }

    function it_outputs_warning_messages(ConsoleOutputInterface $stdOutput, OutputInterface $errorOutput)
    {
        $this->beConstructedWith($stdOutput);
        $stdOutput->getErrorOutput()->willReturn($errorOutput);
        $errorOutput->writeln('<warning>test</>', OutputInterface::VERBOSITY_QUIET)->shouldBeCalled();
        $this->warning('test');
    }

    function it_outputs_error_messages(ConsoleOutputInterface $stdOutput, OutputInterface $errorOutput)
    {
        $this->beConstructedWith($stdOutput);
        $stdOutput->getErrorOutput()->willReturn($errorOutput);
        $errorOutput->writeln('<error>test</>', 0)->shouldBeCalled();
        $this->error('test');
    }

    function it_outputs_fatal_messages(ConsoleOutputInterface $stdOutput, OutputInterface $errorOutput)
    {
        $this->beConstructedWith($stdOutput);
        $stdOutput->getErrorOutput()->willReturn($errorOutput);
        $errorOutput->writeln('<fatal>test</>', 0)->shouldBeCalled();
        $this->fatal('test');
    }

    function it_outputs_newlines(OutputInterface $output)
    {
        $output->writeln('', OutputInterface::VERBOSITY_NORMAL)->shouldBeCalled();
        $this->newLine();
    }

    function it_outputs_timstamps_in_debug(OutputInterface $output)
    {
        Carbon::setTestNow(Carbon::parse('2019-10-06 21:26:12'));
        $output->isDebug()->willReturn(true);
        $output->writeln('<timestamp>[</>2019-10-06 21:26:12<timestamp>]</> <message>test</>', OutputInterface::VERBOSITY_NORMAL)
            ->shouldBeCalled();
        $this->message('test');
    }

    function it_creates_colored_numbered_output(OutputInterface $output)
    {
        $output->writeln('<color1>1</> > test', OutputInterface::VERBOSITY_NORMAL)->shouldBeCalled();
        $this->numberedLine(1, 'test');

        $output->writeln('<color4>4</> > test', OutputInterface::VERBOSITY_NORMAL)->shouldBeCalled();
        $this->numberedLine(4, 'test');

        $output->writeln('<color14>14</> > test', OutputInterface::VERBOSITY_NORMAL)->shouldBeCalled();
        $this->numberedLine(14, 'test');

        $output->writeln('<color2>2</> > test', OutputInterface::VERBOSITY_NORMAL)->shouldBeCalled();
        $this->numberedLine(16, 'test');
    }
}
