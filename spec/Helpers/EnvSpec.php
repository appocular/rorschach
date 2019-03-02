<?php

namespace spec\Rorschach\Helpers;

use Rorschach\Helpers\Env;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnvSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Env::class);
    }

    function it_should_return_value_of_variable()
    {
        putenv('rorschach_test_env_variable=banana');
        $this->get('rorschach_test_env_variable')->shouldReturn('banana');
    }

    function it_should_thow_error_on_unset_variable()
    {
        $exception = new \RuntimeException('slartibartfast env variable not set.');
        $this->shouldThrow($exception)->duringGet('slartibartfast');
    }

    function it_should_use_custom_message_in_exception()
    {
        $exception = new \RuntimeException('deriparamaxx');
        $this->shouldThrow($exception)->duringGet('slartibartfast', 'deriparamaxx');
    }

    function it_should_return_optional_variables()
    {
        putenv('rorschach_test_optional_env_variable=banana');
        $this->getOptional('rorschach_test_optional_env_variable', null)->shouldReturn('banana');
    }

    function it_should_return_default_for_unset_optional_variables()
    {
        $this->getOptional('rorschach_test_optional_env_variable2', null)->shouldReturn(null);
        $this->getOptional('rorschach_test_optional_env_variable2', 'apple')->shouldReturn('apple');
    }
}
