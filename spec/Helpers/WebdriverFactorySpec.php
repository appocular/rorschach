<?php

namespace spec\Rorschach\Helpers;

use Rorschach\Helpers\WebdriverFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WebdriverFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(WebdriverFactory::class);
    }

    function it_should_throw_on_error_connecting()
    {
        $this->shouldThrow(\RuntimeException::class)->duringGet('test', 'banana');
    }
}
