<?php

namespace spec\Rorschach\Appocular;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular\Client;
use Rorschach\Appocular\GuzzleFactory;

class ClientSpec extends ObjectBehavior
{
    function it_should_create_a_batch(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $batchId = 'batch_id';
        $response->getBody()->willReturn(json_encode(['id' => $batchId]));
        $client->post('batch', ['json' => ['sha' => $sha]])->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->beConstructedWith($guzzleFactory);
        $this->createBatch($sha)->shouldReturn($batchId);
    }

    function it_should_delete_a_batch(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $batchId = 'batch_id';
        $response->getStatusCode()->willReturn(200);
        $client->delete('batch/' . $batchId)->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->beConstructedWith($guzzleFactory);
        $this->deleteBatch($batchId)->shouldReturn(true);
    }

    function it_saves_images(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $batchId = 'batch_id';
        $response->getStatusCode()->willReturn(200);
        $json = [
            'name' => 'name',
            'image' => base64_encode('png data'),
        ];
        $client->post('batch/' . $batchId . '/image', $json)->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->beConstructedWith($guzzleFactory);
        $this->snapshot($batchId, 'name', 'png data')->shouldReturn(true);
    }
}
