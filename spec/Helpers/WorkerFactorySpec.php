<?php

namespace spec\Rorschach\Helpers;

use Rorschach\Helpers\WorkerFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WorkerFactorySpec extends ObjectBehavior
{
    function it_should_run_the_command()
    {
        $this->beConstructedWith(['echo', 'here']);

        $this->create([])->shouldReturnWorkerWithOutput('here --worker --ansi');
    }

    function getMatchers(): array
    {
        return [
            'returnWorkerWithOutput' => function ($worker, $string) {
                // Give the process a chance to run.
                usleep(2000);
                return trim($worker->getIncrementalOutput()) == trim($string);
            }
        ];
    }
}
