<?php

namespace spec\Rorschach;

use Facebook\WebDriver\WebDriver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular;
use Rorschach\Appocular\Batch;
use Rorschach\Config;
use Rorschach\Helpers\WebdriverFactory;
use Rorschach\RunCommand;

class RunCommandSpec extends ObjectBehavior
{

    function let(Config $config, WebdriverFactory $webdriverFactory, WebDriver $webdriver, Appocular $appocular, Batch $batch)
    {
        $config->getSha()->willReturn('the sha');
        $config->getBrowserHeight()->willReturn(600);
        $config->getBrowserWidth()->willReturn(800);
        $config->getWebdriverUrl()->willReturn('http://webdriver/');
        $config->getBaseUrl()->willReturn('http://baseurl/');
        $config->getSteps()->willReturn(['front' => '/', 'Page one' => '/one']);

        $appocular->startBatch($webdriver, Argument::any())->willReturn($batch);

        $batch->snapshot(Argument::any())->willReturn(true);

        $batch->close()->willReturn(true);

        $webdriverFactory->get('http://webdriver/', Argument::any())->willReturn($webdriver);

        $this->beConstructedWith($config, $webdriverFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunCommand::class);
    }

    function it_should_require_a_webdriver_url(Config $config, Appocular $appocular, WebdriverFactory $webdriverFactory, WebDriver $webdriver)
    {
        $webdriverFactory->get('http://webdriver/', Argument::any())->willThrow(new \RuntimeException('bad webdriver'));

        $exception = new \RuntimeException(RunCommand::MISSING_WEBDRIVER_URL);
        $config->getWebdriverUrl()->willReturn(null);
        $this->shouldThrow($exception)->during('__invoke', [$appocular]);

        $webdriverFactory->get('webdriver', Argument::any())->willReturn($webdriver);
        $this->callOnWrappedObject('__invoke', [$appocular, 'webdriver']);
    }

    function it_should_require_a_base_url(Config $config, Appocular $appocular, WebdriverFactory $webdriverFactory, WebDriver $webdriver)
    {
        $webdriver->get('http://baseurl/')->shouldBeCalled();
        $webdriver->get('http://baseurl/one')->shouldBeCalled();
        $webdriver->quit()->shouldBeCalled();

        $this->callOnWrappedObject('__invoke', [$appocular]);

        $exception = new \RuntimeException(RunCommand::MISSING_BASE_URL);
        $config->getBaseUrl()->willReturn(null);
        $this->shouldThrow($exception)->during('__invoke', [$appocular]);
    }

    function it_allow_overriding_base_url(Config $config, Appocular $appocular, WebdriverFactory $webdriverFactory, WebDriver $webdriver)
    {
        $webdriver->get('http://baseurl/')->shouldNotBeCalled();
        $webdriver->get('http://baseurl/one')->shouldNotBeCalled();
        $webdriver->get('http://base2/')->shouldBeCalled();
        $webdriver->get('http://base2/one')->shouldBeCalled();
        $webdriver->quit()->shouldBeCalled();

        $this->callOnWrappedObject('__invoke', [$appocular, null, 'http://base2/']);
    }

    function it_should_run_the_validations(Config $config, Appocular $appocular, Batch $batch, Webdriver $webdriver)
    {
        $appocular->startBatch()->willReturn($batch);

        $webdriver->get('http://baseurl/')->shouldBeCalled();
        $batch->snapshot('front')->shouldBeCalled();
        $webdriver->get('http://baseurl/one')->shouldBeCalled();
        $batch->snapshot('Page one')->shouldBeCalled();

        $batch->close()->willReturn(true);
        $webdriver->quit()->shouldBeCalled();
        $this->callOnWrappedObject('__invoke', [$appocular]);
    }
}
