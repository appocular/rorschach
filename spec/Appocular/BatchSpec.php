<?php

namespace spec\Rorschach\Appocular;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular\Batch;
use Rorschach\Appocular\Client;

class BatchSpec extends ObjectBehavior
{
    function it_is_initializable(Client $client)
    {
        $client->createBatch('the sha', null)->willReturn('batch id');
        $this->beConstructedWith($client, 'the sha');
        $this->shouldHaveType(Batch::class);
        $this->getBatchId()->shouldReturn('batch id');
    }

    function it_is_initializable_with_history(Client $client)
    {
        $client->createBatch('the sha', "the\nhistory")->willReturn('batch id');
        $this->beConstructedWith($client, 'the sha', "the\nhistory");
        $this->shouldHaveType(Batch::class);
        $this->getBatchId()->shouldReturn('batch id');
    }

    function it_closes_properly(Client $client)
    {
        $client->createBatch('the sha', null)->willReturn('batch id');
        $client->deleteBatch('batch id')->willReturn(true);
        $this->beConstructedWith($client, 'the sha');
        $this->close()->shouldReturn(true);
    }

    function it_saves_images(Client $client)
    {
        $client->createBatch('the sha', null)->willReturn('batch id');
        $client->checkpoint('batch id', 'name', 'png data')->willReturn(true);
        $this->beConstructedWith($client, 'the sha');
        $this->checkpoint('name', 'png data')->shouldReturn(true);
    }
}
