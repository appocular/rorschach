<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular;
use Rorschach\Appocular\Batch;
use Rorschach\Appocular\Client;

class AppocularSpec extends ObjectBehavior
{
    function it_returns_batch(Client $client)
    {
        $sha = 'the sha';
        $client->createBatch($sha, null)->willReturn('batch_id');
        $this->beConstructedWith($client);
        $this->startBatch($sha)->shouldHaveType(Batch::class);
    }

    function it_returns_batch_with_history(Client $client)
    {
        $sha = 'the sha';
        $history = "the\nhistory";
        $client->createBatch($sha, $history)->willReturn('batch_id');
        $this->beConstructedWith($client);
        $this->startBatch($sha, $history)->shouldHaveType(Batch::class);
    }
}
