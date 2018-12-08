<?php

namespace spec\Rorschach\Appocular;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular\Client;
use Rorschach\Appocular\GuzzleFactory;
use RuntimeException;

class ClientSpec extends ObjectBehavior
{
    function it_should_create_a_batch(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $batchId = 'batch_id';
        $response->getBody()->willReturn(json_encode(['id' => $batchId]));
        $client->post('batch', ['json' => ['id' => $sha]])->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->beConstructedWith($guzzleFactory);
        $this->createBatch($sha)->shouldReturn($batchId);
    }

    function it_should_throw_on_creation_failure(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $client->post('batch', ['json' => ['id' => $sha]])->willThrow(new Exception());
        $guzzleFactory->get()->willReturn($client);
        $this->beConstructedWith($guzzleFactory);
        $this->shouldThrow(RuntimeException::class)->duringCreateBatch($sha);
    }

    function it_should_delete_a_batch(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $batchId = 'batch_id';
        $response->getStatusCode()->willReturn(200);
        $client->delete('batch/' . $batchId)->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->beConstructedWith($guzzleFactory);
        $this->deleteBatch($batchId)->shouldReturn(true);

        // Deleting non-existing batch should throw an error.
        $this->shouldThrow(RuntimeException::class)->duringDeleteBatch('banana');
    }

    function it_saves_checkpoint(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $batchId = 'batch_id';
        $response->getStatusCode()->willReturn(200);
        $json = [
            'name' => 'name',
            'image' => base64_encode('png data'),
        ];
        $client->post('batch/' . $batchId . '/checkpoint', ['json' => $json])->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->beConstructedWith($guzzleFactory);
        $this->checkpoint($batchId, 'name', 'png data')->shouldReturn(true);

        // Should throw on errors.
        $this->shouldThrow(RuntimeException::class)->duringCheckpoint('banana', 'name', 'png data');
    }
}
