<?php

namespace spec\Rorschach;

use Facebook\WebDriver\WebDriver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular;
use Rorschach\Appocular\Batch;
use Rorschach\Appocular\Client;

class AppocularSpec extends ObjectBehavior
{
    function it_returns_batch(Client $client, WebDriver $webDriver)
    {
        $sha = 'the sha';
        $client->createBatch($sha)->willReturn('batch_id');
        $this->beConstructedWith($client);
        $this->startBatch($webDriver, $sha)->shouldHaveType(Batch::class);
    }
}
