<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Config;
use Rorschach\Helpers\ConfigFile;
use Rorschach\Helpers\Env;
use Rorschach\Helpers\Git;
use Symfony\Component\Console\Input\InputInterface;

class ConfigSpec extends ObjectBehavior
{

    function let(Env $env, ConfigFile $configFile, InputInterface $input, Git $git)
    {
        // A working configuration.
        $env->get('GITHUB_SHA', Config::MISSING_SHA_ERROR)->willReturn('github sha');
        $env->get('APPOCULAR_TOKEN', Config::MISSING_TOKEN_ERROR)->willReturn('appocular-token');
        $env->getOptional('RORSCHACH_HISTORY', null)->willReturn(null);

        $configFile->getWebdriverUrl()->willReturn('webdriver-url');
        $configFile->getBaseUrl()->willReturn('base-url');
        $configFile->getSteps()->willReturn(['one' => '1', 'two' => '2']);

        $this->beConstructedWith($env, $configFile, $input, $git);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Config::class);
    }

    function it_throw_on_missing_or_empty_sha(Env $env)
    {
        $exception = new \RuntimeException(Config::MISSING_SHA_ERROR);
        $env->get('GITHUB_SHA', Config::MISSING_SHA_ERROR)->willThrow($exception);
        $env->get('CIRCLE_SHA1', Config::MISSING_SHA_ERROR)->willThrow($exception);
        $this->shouldThrow($exception)->duringInstantiation();

        $env->get('GITHUB_SHA', Config::MISSING_SHA_ERROR)->willReturn('');
        $this->shouldThrow($exception)->duringInstantiation();
    }

    function it_should_provide_a_sha_from_github()
    {
        $this->getSha()->shouldReturn('github sha');
    }

    function it_should_provide_a_sha_from_circle(Env $env)
    {
        $env->get('GITHUB_SHA', Config::MISSING_SHA_ERROR)->willThrow(\RuntimeException::class);
        $env->get('CIRCLE_SHA1', Config::MISSING_SHA_ERROR)->willReturn('circle sha');
        $this->getSha()->shouldReturn('circle sha');
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

    function it_should_provide_steps()
    {
        $this->getSteps()->shouldReturn(['one' => '1', 'two' => '2']);
    }

    function it_should_provide_a_webdriver_url(ConfigFile $configFile, InputInterface $input)
    {
        // From config.
        $this->getWebdriverUrl()->shouldReturn('webdriver-url');

        // From command line.
        $input->getOption('webdriver')->willReturn('new-webdriver-url');
        $this->getWebdriverUrl()->shouldReturn('new-webdriver-url');

        // Should error if neither config or command line arg is set.
        $exception = new \RuntimeException(Config::MISSING_WEBDRIVER_URL);
        $configFile->getWebdriverUrl()->willReturn(null);
        $input->getOption('webdriver')->willReturn(null);
        $this->shouldThrow($exception)->duringGetWebdriverUrl();
    }

    function it_should_provide_a_base_url(ConfigFile $configFile, InputInterface $input)
    {
        // From config.
        $this->getBaseUrl()->shouldReturn('base-url');

        // From command line.
        $input->getOption('base-url')->willReturn('new-base-url');
        $this->getBaseUrl()->shouldReturn('new-base-url');

        // Should error if neither config or command line arg is set.
        $exception = new \RuntimeException(Config::MISSING_BASE_URL);
        $configFile->getBaseUrl()->willReturn(null);
        $input->getOption('base-url')->willReturn(null);
        $this->shouldThrow($exception)->duringGetBaseUrl();
    }

    function it_should_strip_trailling_slashes_on_base_url(ConfigFile $configFile, InputInterface $input)
    {
        $configFile->getBaseUrl()->willReturn('/base-url///');
        $this->getBaseUrl()->shouldReturn('/base-url');

        $input->getOption('base-url')->willReturn('/new-base-url///');
        $this->getBaseUrl()->shouldReturn('/new-base-url');
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

    function it_can_get_history_from_git(Git $git)
    {
        $git->getHistory()->willReturn("sha1\nsha2\n");

        $this->getHistory()->shouldReturn("sha1\nsha2\n");
    }

    function it_should_let_history_env_var_override_git(Env $env, Git $git)
    {
        $env->getOptional('RORSCHACH_HISTORY', null)->willReturn("a\nhistory");
        $git->getHistory()->willReturn("sha1\nsha2\n");
        $this->getHistory()->shouldReturn("a\nhistory");
    }

    function it_can_provide_a_writeout_directory(InputInterface $input)
    {
        $input->getOption('write-out')->willReturn('the_dir');
        $this->getWriteOut()->shouldReturn('the_dir');
    }

    function it_can_provide_a_readin_directory(InputInterface $input)
    {
        $input->getOption('read-in')->willReturn('the_dir');
        $this->getReadIn()->shouldReturn('the_dir');
    }

    function it_should_provide_a_base(ConfigFile $configFile, InputInterface $input)
    {
        $input->getOption('base')->willReturn(false);
        $this->getBase()->shouldReturn('alpha.appocular.io');

        $input->getOption('base')->willReturn(true);
        $input->getOption('base')->willReturn('example.com');
        $this->getBase()->shouldReturn('example.com');
    }
}
