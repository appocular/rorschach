<?php

namespace spec\Rorschach;

use Applitools\Selenium\Eyes;
use Applitools\RectangleSize;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Config;
use Rorschach\Helpers\WebdriverFactory;
use Rorschach\RunCommand;
use Facebook\WebDriver\WebDriver;

class RunCommandSpec extends ObjectBehavior
{

    function let(Config $config, Eyes $eyes, WebdriverFactory $webdriverFactory, WebDriver $webdriver)
    {
        $config->getApplitoolsApiKey()->willReturn('eyes-key');
        $config->getApplitoolsBatchId()->willReturn('eyes-batch');
        $config->getAppName()->willReturn('app-name');
        $config->getTestName()->willReturn('test-name');
        $config->getBrowserHeight()->willReturn(600);
        $config->getBrowserWidth()->willReturn(800);

        $webdriverFactory->get(Argument::any(), Argument::any())->willReturn($webdriver);

        $this->beConstructedWith($config, $eyes, $webdriverFactory);
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
        // Should throw an error when batch id is unavailable.
        $exception = new \RuntimeException(Config::MISSING_BATCH_ID_ERROR);
        $config->getApplitoolsBatchId()->willThrow($exception);
        $this->shouldThrow($exception)->during('__invoke', ['']);
    }

    // Work in progress.
    function it_should_provide_proper_configuration_to_eyes(Config $config, Eyes $eyes, Webdriver $webdriver)
    {
        $eyes->setApiKey('eyes-key')->shouldBeCalled();
        $eyes->setBatch(Argument::that(function ($batch) {
            return $batch->getId() === 'eyes-batch';
        }))->shouldBeCalled();

        $eyes->open($webdriver, 'app-name', 'test-name', new RectangleSize(800, 600))->shouldBeCalled();
        $eyes->close(false)->shouldBeCalled();
        $this->callOnWrappedObject('__invoke', ['']);
    }
}
