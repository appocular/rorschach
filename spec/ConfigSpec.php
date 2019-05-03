<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Config;
use Rorschach\Helpers\ConfigFile;
use Rorschach\Helpers\Env;

class ConfigSpec extends ObjectBehavior
{

    function let(Env $env, ConfigFile $configFile)
    {
        // A working configuration.
        $env->get('CIRCLE_SHA1', Config::MISSING_SHA_ERROR)->willReturn('sha');
        $env->get('APPOCULAR_TOKEN', Config::MISSING_TOKEN_ERROR)->willReturn('appocular-token');
        $env->getOptional('RORSCHACH_HISTORY', null)->willReturn(null);

        $configFile->getBrowserHeight()->willReturn(600);
        $configFile->getBrowserWidth()->willReturn(800);
        $configFile->getWebdriverUrl()->willReturn('webdriver-url');
        $configFile->getBaseUrl()->willReturn('base-url');
        $configFile->getSteps()->willReturn(['one' => '1', 'two' => '2']);

        $this->beConstructedWith($env, $configFile);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Config::class);
    }

    function it_throw_on_missing_or_empty_sha(Env $env)
    {
        $exception = new \RuntimeException(Config::MISSING_SHA_ERROR);
        $env->get('CIRCLE_SHA1', Config::MISSING_SHA_ERROR)->willThrow($exception);
        $this->shouldThrow($exception)->duringInstantiation();

        $env->get('CIRCLE_SHA1', Config::MISSING_SHA_ERROR)->willReturn('');
        $this->shouldThrow($exception)->duringInstantiation();
    }

    function it_should_provide_a_sha()
    {
        $this->getSha()->shouldReturn('sha');
    }

    function it_throw_on_missing_or_empty_token(Env $env)
    {
        $exception = new \RuntimeException(Config::MISSING_TOKEN_ERROR);
        $env->get('APPOCULAR_TOKEN', Config::MISSING_TOKEN_ERROR)->willThrow($exception);
        $this->shouldThrow($exception)->duringInstantiation();

        $env->get('APPOCULAR_TOKEN', Config::MISSING_TOKEN_ERROR)->willReturn('');
        $this->shouldThrow($exception)->duringInstantiation();
    }

    function it_should_provide_a_token()
    {
        $this->getToken()->shouldReturn('appocular-token');
    }

    function it_should_provide_browser_size()
    {
        $this->getBrowserHeight()->shouldReturn(600);
        $this->getBrowserWidth()->shouldReturn(800);
    }

    function it_should_provide_steps()
    {
        $this->getSteps()->shouldReturn(['one' => '1', 'two' => '2']);
    }

    function it_con_provide_a_webdriver_url(ConfigFile $configFile)
    {
        $this->getWebdriverUrl()->shouldReturn('webdriver-url');
        $configFile->getWebdriverUrl()->willReturn(null);
        $this->getWebdriverUrl()->shouldReturn(null);
    }

    function it_con_provide_a_base_url(ConfigFile $configFile)
    {
        $this->getBaseUrl()->shouldReturn('base-url');
        $configFile->getBaseUrl()->willReturn(null);
        $this->getBaseUrl()->shouldReturn(null);
    }

    function it_should_return_null_for_no__history(Env $env)
    {
        $this->getHistory()->shouldReturn(null);
    }

    function it_can_provide_a_history(Env $env)
    {
        $env->getOptional('RORSCHACH_HISTORY', null)->willReturn("a\nhistory");
        $this->getHistory()->shouldReturn("a\nhistory");
    }
}
