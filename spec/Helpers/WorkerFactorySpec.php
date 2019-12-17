<?php

declare(strict_types=1);

namespace spec\Rorschach\Helpers;

use PhpSpec\ObjectBehavior;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// phpcs:disable Squiz.Scope.MethodScope.Missing
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
class WorkerFactorySpec extends ObjectBehavior
{
    function it_should_run_the_command()
    {
        $this->beConstructedWith(['echo', 'here']);

        $this->create([])->shouldReturnWorkerWithOutput('here --worker --ansi');
    }

    /**
     * @return array<string, \Closure>
     */
    function getMatchers(): array
    {
        return [
            'returnWorkerWithOutput' => static function ($worker, $string) {
                // Give the process a chance to run.
                \usleep(5000);

                return \trim($worker->getIncrementalOutput()) === \trim($string);
            },
        ];
    }
}
