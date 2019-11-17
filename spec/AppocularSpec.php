<?php

declare(strict_types=1);

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Rorschach\Appocular\Batch;
use Rorschach\Appocular\Client;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// phpcs:disable Squiz.Scope.MethodScope.Missing
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
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
