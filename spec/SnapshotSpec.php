<?php

namespace spec\Rorschach;

use Facebook\WebDriver\WebDriver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular;
use Rorschach\Appocular\Batch;
use Rorschach\Config;
use Rorschach\Snapshot;

class SnapshotSpec extends ObjectBehavior
{

    function let(Config $config, WebDriver $webdriver, Appocular $appocular, Batch $batch)
    {
        $config->getSha()->willReturn('the sha');
        $config->getToken()->willReturn('the token');
        $config->getBrowserHeight()->willReturn(600);
        $config->getBrowserWidth()->willReturn(800);
        $config->getWebdriverUrl()->willReturn('http://webdriver/');
        $config->getBaseUrl()->willReturn('http://baseurl');
        $config->getSteps()->willReturn(['front' => '/', 'Page one' => '/one']);
        $config->getHistory()->willReturn(null);

        $appocular->startBatch(Argument::any(), Argument::any())->willReturn($batch);

        $batch->checkpoint(Argument::any(), Argument::any())->willReturn(true);

        $batch->close()->willReturn(true);

        $this->beConstructedWith($config, $appocular, $webdriver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Snapshot::class);
    }

    function it_should_run_the_validations(Config $config, Appocular $appocular, Batch $batch, Webdriver $webdriver)
    {
        $appocular->startBatch()->willReturn($batch);

        $webdriver->get('http://baseurl/')->shouldBeCalled();
        $webdriver->takeScreenshot()->willReturn('png data', 'more png data')->shouldBeCalled();
        $batch->checkpoint('front', 'png data')->shouldBeCalled();
        $webdriver->get('http://baseurl/one')->shouldBeCalled();
        $batch->checkpoint('Page one', 'more png data')->shouldBeCalled();

        $batch->close()->willReturn(true);
        $webdriver->quit()->shouldBeCalled();

        $this->getWrappedObject()->run();
    }

    function it_should_pass_the_history(Config $config, Appocular $appocular, Batch $batch, Webdriver $webdriver)
    {
        $history = "the\nhistroy";
        $config->getHistory()->willReturn($history);
        // No steps, to avoid calls on $webdriver.
        $config->getSteps()->willReturn([]);
        $appocular->startBatch('the sha', $history)->shouldBeCalled()->willReturn($batch);
        $batch->close()->willReturn(true);

        $this->getWrappedObject()->run();
    }

    // it_should_skip_failed_screenshots
    // when takeScreenshot returns null.
}
