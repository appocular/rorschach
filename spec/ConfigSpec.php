<?php

namespace spec\Rorschach;

use Rorschach\Config;
use Rorschach\Helpers\Env;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigSpec extends ObjectBehavior
{

    function let(Env $env)
    {
        // A working configuration.
        $env->get('APPLITOOLS_API_KEY', Config::MISSING_API_KEY_ERROR)->willReturn('api_key_value');
        $env->get('CIRCLE_SHA1', Config::MISSING_BATCH_ID_ERROR)->willReturn('batch_id_value');
        $this->beConstructedWith($env);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Config::class);
    }


    function it_throw_on_missing_or_empty_api_key(Env $env)
    {
        $exception = new \RuntimeException(Config::MISSING_API_KEY_ERROR);
        $env->get('APPLITOOLS_API_KEY', Config::MISSING_API_KEY_ERROR)->willThrow($exception);
        $this->shouldThrow($exception)->duringInstantiation();

        $env->get('APPLITOOLS_API_KEY', Config::MISSING_API_KEY_ERROR)->willReturn('');
        $this->shouldThrow($exception)->duringInstantiation();
    }

    function it_should_provide_an_api_key(Env $env)
    {
        $this->getApplitoolsApiKey()->shouldReturn('api_key_value');
    }

    function it_throw_on_missing_or_empty_batch_id(Env $env)
    {
        $exception = new \RuntimeException(Config::MISSING_BATCH_ID_ERROR);
        $env->get('CIRCLE_SHA1', Config::MISSING_BATCH_ID_ERROR)->willThrow($exception);
        $this->shouldThrow($exception)->duringInstantiation();

        $env->get('CIRCLE_SHA1', Config::MISSING_BATCH_ID_ERROR)->willReturn('');
        $this->shouldThrow($exception)->duringInstantiation();
    }

    function it_should_provide_a_batch_id(Env $env)
    {
        $this->getApplitoolsBatchId()->shouldReturn('batch_id_value');
    }
}
