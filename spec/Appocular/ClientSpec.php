<?php

namespace spec\Rorschach\Appocular;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Appocular\Client;
use Rorschach\Config;
use RuntimeException;

class ClientSpec extends ObjectBehavior
{
    function let(GuzzleClient $client, Config $config) {
        $config->getToken()->willReturn('the-token');
        $this->beConstructedWith($client, $config);
    }

    /**
     * Options expected on all requests.
     */
    function requestOptions() {
        return [
            'headers' => ['Authorization' => 'Bearer the-token'],
            'allow_redirects' => false,
        ];
    }

    function it_should_create_a_batch(GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $batchId = 'http://host/batch/batch_id';
        $response->getStatusCode()->willReturn(201);
        $response->hasHeader('Location')->willReturn(true);
        $response->getHeader('Location')->willReturn([$batchId]);
        $client->post('batch', ['json' => ['id' => $sha]] + $this->requestOptions())->willReturn($response);
        $this->createBatch($sha)->shouldReturn($batchId);
    }

    function it_should_throw_on_creation_failure(GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $client->post('batch', ['json' => ['id' => $sha]] + $this->requestOptions())->willThrow(new Exception());
        $this->shouldThrow(RuntimeException::class)->duringCreateBatch($sha);
    }

    function it_should_delete_a_batch(GuzzleClient $client, Response $response)
    {
        $batchId = 'http://host/batch/batch_id';
        $response->getStatusCode()->willReturn(200);
        $client->delete($batchId, $this->requestOptions())->willReturn($response);
        $this->deleteBatch($batchId)->shouldReturn(true);

        // Deleting non-existing batch should throw an error.
        $this->shouldThrow(RuntimeException::class)->duringDeleteBatch('banana');
    }

    function it_saves_checkpoint(GuzzleClient $client, Response $response)
    {
        $batchId = 'http://host/batch/batch_id';
        $response->getStatusCode()->willReturn(200);
        $json = [
            'name' => 'name',
            'image' => base64_encode('png data'),
        ];
        $client->post($batchId . '/checkpoint', ['json' => $json] + $this->requestOptions())->willReturn($response);
        $this->checkpoint($batchId, 'name', 'png data')->shouldReturn(true);

        // Should throw on errors.
        $this->shouldThrow(RuntimeException::class)->duringCheckpoint('banana', 'name', 'png data');
    }

    function it_passes_history(GuzzleClient $client, Response $response)
    {
        $sha = 'the sha';
        $batchId = 'http://host/batch/batch_id';
        $history = "a\nhistory\nwith\nmultiple\nlines";
        $response->getStatusCode()->willReturn(201);
        $response->hasHeader('Location')->willReturn(true);
        $response->getHeader('Location')->willReturn([$batchId]);
        $client->post('batch', ['json' => ['id' => $sha, 'history' => $history]] + $this->requestOptions())->willReturn($response);
        $this->createBatch($sha, $history)->shouldReturn($batchId);
    }

    // todo: checkpoint and close should return void and throw on errors
}
