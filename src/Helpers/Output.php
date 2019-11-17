<?php

declare(strict_types=1);

namespace Rorschach\Helpers;

use Carbon\Carbon;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Output
{
    /**
     * Output to write to.
     *
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
        $formatter->setStyle('fatal', new OutputFormatterStyle('white', 'red'));
        $formatter->setStyle('timestamp', new OutputFormatterStyle('white'));

        $colors = [
            'blue',
            'magenta',
            'cyan',
            'default',
            'red',
            'green',
            'yellow',
        ];

        $i = 1;

        for ($i = 0; $i < \count($colors); $i++) {
            $formatter->setStyle('color' . ($i + 1), new OutputFormatterStyle($colors[$i]));
            $formatter->setStyle(
                'color' . (($i + 1) + \count($colors)),
                new OutputFormatterStyle($colors[$i], null, ['reverse']),
            );
        }
    }

    public function debug(string $message): void
    {
        $this->writeln('debug', $message, OutputInterface::VERBOSITY_DEBUG);
    }

    public function message(string $message): void
    {
        $this->writeln('message', $message, OutputInterface::VERBOSITY_NORMAL);
    }

    public function info(string $message): void
    {
        $this->writeln('info', $message, OutputInterface::VERBOSITY_VERBOSE);
    }

    public function warning(string $message): void
    {
        $this->writeln('warning', $message, OutputInterface::VERBOSITY_QUIET, true);
    }

    public function error(string $message): void
    {
        $this->writeln('error', $message, 0, true);
    }

    public function fatal(string $message): void
    {
        $this->writeln('fatal', $message, 0, true);
    }

    public function newLine(): void
    {
        $this->output->writeln('', OutputInterface::VERBOSITY_NORMAL);
    }

    public function numberedLine(int $num, string $message): void
    {
        $colorIndex = (($num - 1) % 14 ) + 1;
        $message = \sprintf('<color%d>%d</> > %s', $colorIndex, $colorIndex, $message);
        $this->output->writeln($message, OutputInterface::VERBOSITY_NORMAL);
    }

    protected function writeln(string $style, string $message, int $verbosity, bool $errorOutput = false): void
    {
        $message = \sprintf("<%s>%s</>", $style, $message);

        // Add timestamps in debug mode.
        if ($this->output->isDebug()) {
            $message = \sprintf("<timestamp>[</>%s<timestamp>]</> %s", Carbon::now()->format('Y-m-d H:i:s'), $message);
        }

        // Print errors to stderr.
        if ($errorOutput && $this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->writeln($message, $verbosity);
        } else {
            $this->output->writeln($message, $verbosity);
        }
    }
}
