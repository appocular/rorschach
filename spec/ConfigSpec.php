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
        $env->get('APPLITOOLS_API_KEY', Config::MISSING_API_KEY_ERROR)->willReturn('api_key_value');
        $env->get('CIRCLE_SHA1', Config::MISSING_BATCH_ID_ERROR)->willReturn('batch_id_value');

        $configFile->getAppName()->willReturn('app_name');
        $configFile->getTestName()->willReturn('test_name');
        $configFile->getBrowserHeight()->willReturn(600);
        $configFile->getBrowserWidth()->willReturn(800);
        $configFile->getWebdriverUrl()->willReturn('webdriver-url');
        $configFile->getSteps()->willReturn(['one' => '1', 'two' => '2']);

        $this->beConstructedWith($env, $configFile);
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

    function it_should_provide_an_api_key()
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

    function it_should_provide_a_batch_id()
    {
        $this->getApplitoolsBatchId()->shouldReturn('batch_id_value');
    }

    function it_should_provide_an_app_name()
    {
        $this->getAppName()->shouldReturn('app_name');
    }

    function it_should_provide_a_test_name()
    {
        $this->getTestName()->shouldReturn('test_name');
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
}
