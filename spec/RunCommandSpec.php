<?php

namespace spec\Rorschach;

use Applitools\Selenium\Eyes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Config;
use Rorschach\RunCommand;

class RunCommandSpec extends ObjectBehavior
{

    function let(Config $config, Eyes $eyes)
    {
        $this->beConstructedWith($config, $eyes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunCommand::class);
    }

    function it_should_require_an_eyes_api_key(Config $config, Eyes $eyes)
    {
        // Should throw an error when API key is unavailable.
        $exception = new \RuntimeException(Config::MISSING_API_KEY_ERROR);
        $config->getApplitoolsApiKey()->willThrow($exception);
        $this->shouldThrow($exception)->during('__invoke', ['']);
    }

    function it_should_require_an_eyes_batch_id(Config $config, Eyes $eyes)
    {
        $config->getApplitoolsApiKey()->willReturn('eyes-key');

        // Should throw an error when batch id is unavailable.
        $exception = new \RuntimeException(Config::MISSING_BATCH_ID_ERROR);
        $config->getApplitoolsBatchId()->willThrow($exception);
        $this->shouldThrow($exception)->during('__invoke', ['']);
    }

    // Work in progress.
    function it_should_provide_key_and_batch_id_to_eyes(Config $config, Eyes $eyes)
    {
        $config->getApplitoolsApiKey()->willReturn('eyes-key');
        $config->getApplitoolsBatchId()->willReturn('eyes-batch');

        $eyes->setApiKey('eyes-key')->shouldBeCalled();
        $eyes->setBatch(Argument::that(function ($batch) {
            return $batch->getId() === 'eyes-batch';
        }))->shouldBeCalled();

        $this->callOnWrappedObject('__invoke', ['']);
    }
}
