<?php

namespace spec\Rorschach\Appocular;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular\Client;
use Rorschach\Appocular\GuzzleFactory;
use Rorschach\Config;
use RuntimeException;

class ClientSpec extends ObjectBehavior
{
    function let(GuzzleFactory $guzzleFactory, Config $config) {
        $config->getToken()->willReturn('the-token');
        $this->beConstructedWith($guzzleFactory, $config);
    }

    /**
     * Options expected on all requests.
     */
    function requestOptions() {
        return ['headers' => ['Authorization' => 'Bearer the-token']];
    }

    function it_should_create_a_batch(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $batchId = 'batch_id';
        $response->getBody()->willReturn(json_encode(['id' => $batchId]));
        $client->post('batch', ['json' => ['id' => $sha]] + $this->requestOptions())->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->createBatch($sha)->shouldReturn($batchId);
    }

    function it_should_throw_on_creation_failure(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $client->post('batch', ['json' => ['id' => $sha]] + $this->requestOptions())->willThrow(new Exception());
        $guzzleFactory->get()->willReturn($client);
        $this->shouldThrow(RuntimeException::class)->duringCreateBatch($sha);
    }

    function it_should_delete_a_batch(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $batchId = 'batch_id';
        $response->getStatusCode()->willReturn(200);
        $client->delete('batch/' . $batchId, $this->requestOptions())->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
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
        $client->post('batch/' . $batchId . '/checkpoint', ['json' => $json] + $this->requestOptions())->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->checkpoint($batchId, 'name', 'png data')->shouldReturn(true);

        // Should throw on errors.
        $this->shouldThrow(RuntimeException::class)->duringCheckpoint('banana', 'name', 'png data');
    }

    function it_passes_history(GuzzleFactory $guzzleFactory, GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $batchId = 'batch_id';
        $history = "a\nhistory\nwith\nmultiple\nlines";
        $response->getBody()->willReturn(json_encode(['id' => $batchId]));
        $client->post('batch', ['json' => ['id' => $sha, 'history' => $history]] + $this->requestOptions())->willReturn($response);
        $guzzleFactory->get()->willReturn($client);
        $this->createBatch($sha, $history)->shouldReturn($batchId);
    }
}
