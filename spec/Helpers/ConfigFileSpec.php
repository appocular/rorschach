<?php

namespace spec\Rorschach\Helpers;

use Rorschach\Helpers\ConfigFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigFileSpec extends ObjectBehavior
{
    // Need to declare these properties, else PhpSpec will try to propergate
    // them to the object being tested.
    protected $fixtures;
    protected $oldPwd;

    function __construct()
    {
        $this->fixtures = dirname(dirname(__DIR__)) . '/fixtures';
    }

    function useFixture($name)
    {
        if (!isset($this->oldPwd)) {
            $this->oldPwd = getcwd();
        }
        chdir($this->fixtures . '/' . $name);
    }

    function letGo()
    {
        // Undo useFixture.
        if (isset($this->oldPwd)) {
            chdir($this->oldPwd);
        }
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConfigFile::class);
    }

    function it_should_error_on_no_config_file()
    {
        // Set fixture to make letGo reset it.
        $this->useFixture('config');
        chdir('/');
        $this->shouldThrow(new \RuntimeException(ConfigFile::MISSING_CONFIG_FILE_ERROR));
    }

    function it_should_find_config_file_current_dir()
    {
        $this->useFixture('config');
        $this->getAppName()->shouldReturn('Testapp');
    }

    function it_should_find_config_from_subdir()
    {
        $this->useFixture('config');
        // Create test sub-directory, so we don't have to commit an file to
        // make it exist.
        if (!file_exists('sub/dir')) {
            mkdir('sub/dir', 0777, true);
        }
        chdir('sub/dir');
        $this->getAppName()->shouldReturn('Testapp');
    }

    function it_should_error_on_malformed_file()
    {
        $this->useFixture('corrupted');
        // Not testing for specific error message, as that depends on Symfony::YAML.
        $this->shouldThrow(\RuntimeException::class)->duringInstantiation();
    }

    function it_should_return_app_name()
    {
        $this->useFixture('config');
        $this->getAppName()->shouldReturn('Testapp');
    }

    function it_should_return_test_name()
    {
        $this->useFixture('config');
        $this->getTestName()->shouldReturn('Testname');
    }

    function it_should_require_proper_configuration()
    {
        $this->useFixture('empty');
        $errors = [
            ConfigFile::MISSING_APP_NAME_ERROR,
            ConfigFile::MISSING_TEST_NAME_ERROR,
        ];
        $exception = new \RuntimeException(implode('\n', $errors));
        $this->shouldThrow($exception)->duringInstantiation();
    }
}
