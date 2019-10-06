<?php

namespace Rorschach\Helpers;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Output
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $formatter = $this->output->getFormatter();

        $formatter->setStyle('debug', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('message', new OutputFormatterStyle());
        $formatter->setStyle('info', new OutputFormatterStyle('magenta'));
        $formatter->setStyle('warning', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('error', new OutputFormatterStyle('red'));
    }

    public function debug($message)
    {
        $this->writeln('debug', $message, OutputInterface::VERBOSITY_DEBUG);
    }

    public function message($message)
    {
        $this->writeln('message', $message, OutputInterface::VERBOSITY_NORMAL);
    }

    public function info($message)
    {
        $this->writeln('info', $message, OutputInterface::VERBOSITY_VERBOSE);
    }

    public function warning($message)
    {
        $this->writeln('warning', $message, OutputInterface::VERBOSITY_QUIET, true);
    }

    public function error($message)
    {
        $this->writeln('error', $message, 0, true);
    }

    public function newline()
    {
        $this->output->writeln('', OutputInterface::VERBOSITY_NORMAL);
    }

    protected function writeln($style, $message, $verbosity, $errorOutput = false)
    {
        if ($errorOutput && $this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->writeln(sprintf("<%s>%s</>", $style, $message), $verbosity);
        } else {
            $this->output->writeln(sprintf("<%s>%s</>", $style, $message), $verbosity);
        }
    }
}
