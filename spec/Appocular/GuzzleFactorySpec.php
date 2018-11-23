<?php

namespace spec\Rorschach\Appocular;

use GuzzleHttp\Client;
use Rorschach\Appocular\GuzzleFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GuzzleFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GuzzleFactory::class);
    }

    function it_should_return_a_guzzle_client()
    {
        $this->get()->shouldHaveType(Client::class);
    }
}
