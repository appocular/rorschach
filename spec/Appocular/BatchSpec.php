<?php

namespace spec\Rorschach\Appocular;

use Facebook\WebDriver\WebDriver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular\Batch;
use Rorschach\Appocular\Client;

class BatchSpec extends ObjectBehavior
{
    function it_is_initializable(WebDriver $webDriver, Client $client)
    {
        $client->createBatch('the sha')->willReturn('batch id');
        $this->beConstructedWith($webDriver, $client, 'the sha');
        $this->shouldHaveType(Batch::class);
        $this->getBatchId()->shouldReturn('batch id');
    }

    function it_closes_properly(WebDriver $webDriver, Client $client)
    {
        $client->createBatch('the sha')->willReturn('batch id');
        $client->deleteBatch('batch id')->willReturn(true);
        $this->beConstructedWith($webDriver, $client, 'the sha');
        $this->close()->shouldReturn(true);
    }

    function it_saves_images(WebDriver $webDriver, Client $client)
    {
        $client->createBatch('the sha')->willReturn('batch id');
        $client->checkpoint('batch id', 'name', 'png data')->willReturn(true);
        $webDriver->takeScreenshot()->willReturn('png data');
        $this->beConstructedWith($webDriver, $client, 'the sha');
        $this->checkpoint('name')->shouldReturn(true);
    }
}
