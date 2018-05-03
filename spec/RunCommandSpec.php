<?php

namespace spec\Rorschach;

use Rorschach\RunCommand;
use Rorschach\Config;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RunCommandSpec extends ObjectBehavior
{

    function let(Config $config)
    {
        $this->beConstructedWith($config);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunCommand::class);
    }

    function it_should_require_an_api_key(Config $config)
    {
        $exception = new \RuntimeException(Config::MISSING_API_KEY_ERROR);
        $config->getApplitoolsApiKey()->willThrow($exception);
        $this->shouldThrow($exception)->during('__invoke', ['']);
    }
}
