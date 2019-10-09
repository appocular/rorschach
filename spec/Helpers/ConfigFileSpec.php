<?php

namespace spec\Rorschach\Helpers;

use Rorschach\Helpers\ConfigFile;
use Rorschach\Step;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigFileSpec extends ObjectBehavior
{
    // Need to declare these properties, else PhpSpec will try to propergate
    // them to the object being tested.
    protected $fixtures;
    protected $oldPwd;
    protected $dir;

    // Defaults added to all steps.
    protected $stepDefaults = [
        'browser_height' => ConfigFile::DEFAULT_BROWSER_HEIGHT,
        'browser_width' => ConfigFile::DEFAULT_BROWSER_WIDTH,
    ];

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

    function withFixture($yaml)
    {
        $this->dir = sys_get_temp_dir() . '/rorschach-test-' . getmypid();
        mkdir($this->dir);
        \file_put_contents($this->dir . '/rorschach.yml', $yaml);

        if (!isset($this->oldPwd)) {
            $this->oldPwd = getcwd();
        }
        chdir($this->dir);
    }

    function letGo()
    {
        // Undo useFixture.
        if (isset($this->oldPwd)) {
            chdir($this->oldPwd);
        }

        // Clean up after withFixture.
        if (isset($this->dir) && \file_exists($this->dir)) {
            unlink($this->dir . '/rorschach.yml');
            rmdir($this->dir);
        }
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConfigFile::class);
    }

    function it_should_error_on_no_config_file()
    {
        // Set fixture to make letGo reset it.
        $this->useFixture('minimal');
        chdir('/');
        $this->shouldThrow(new \RuntimeException(ConfigFile::MISSING_CONFIG_FILE_ERROR));
    }

    function it_should_find_config_file_current_dir()
    {
        $this->useFixture('minimal');
        $this->getSteps()->shouldBeLike([new Step('first', '/', $this->stepDefaults)]);
    }

    function it_should_find_config_from_subdir()
    {
        $this->useFixture('minimal');
        // Create test sub-directory, so we don't have to commit an file to
        // make it exist.
        if (!file_exists('sub/dir')) {
            mkdir('sub/dir', 0777, true);
        }
        chdir('sub/dir');
    }

    function it_should_error_on_malformed_file()
    {
        $this->useFixture('corrupted');
        // Not testing for specific error message, as that depends on Symfony::YAML.
        $this->shouldThrow(\RuntimeException::class)->duringInstantiation();
    }

    function it_should_require_proper_configuration()
    {
        $this->useFixture('empty');
        $exception = new \RuntimeException(ConfigFile::NO_STEPS_ERROR);
        $this->shouldThrow($exception)->duringInstantiation();
    }

    function it_should_return_steps()
    {
        $this->useFixture('full');
        $this->getSteps()->shouldBeLike([
            new Step('front', '/', $this->stepDefaults),
            new Step('Page two', '/two', $this->stepDefaults),
            new Step('three', '/slashless', $this->stepDefaults),
        ]);
    }

    function it_should_return_webdriver_url()
    {
        $this->useFixture('full');
        $this->getWebdriverUrl()->shouldReturn('http://wd:4444/wd/hub');
    }

    function it_should_return_base_url()
    {
        $this->useFixture('full');
        $this->getBaseUrl()->shouldReturn('http://localhost/');
    }

    function it_should_handle_complex_steps()
    {
        $yaml = <<<'EOF'
steps:
  front: /
  "with-path":
    path: /the-path
EOF;
        $this->withFixture($yaml);
        $this->getSteps()->shouldBeLike([
            new Step('front', '/', $this->stepDefaults),
            new Step('with-path', '/the-path', $this->stepDefaults),
        ]);
    }

    function it_should_handle_step_defaults()
    {
        $yaml = <<<'EOF'
defaults:
  hide:
    cookiepopup: "#cookiepopup"
steps:
  front: /
  "with-path":
    path: /the-path
EOF;
        $this->withFixture($yaml);
        $this->getSteps()->shouldBeLike([
            new Step('front', ['path' => '/', 'hide' => ['cookiepopup' => '#cookiepopup']] + $this->stepDefaults),
            new Step('with-path', ['path' => '/the-path', 'hide' => ['cookiepopup' => '#cookiepopup']] + $this->stepDefaults),
        ]);
    }

    function it_should_return_workers()
    {
        $yaml = <<<'EOF'
workers: 8
steps:
  front: /
EOF;
        $this->withFixture($yaml);
        $this->getWorkers()->shouldReturn(8);
    }
}
