<?php

namespace Rorschach\Appocular;

use Exception;
use GuzzleHttp\Client as Guzzle;
use Rorschach\Config;
use RuntimeException;

class Client
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \Rorschach\Config
     */
    protected $config;

    /**
     * Construct a new client.
     *
     * @param Guzzle $client
     *   Guzzle client.
     */
    public function __construct(Guzzle $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Get the request options with authorization header.
     */
    protected function getOptions()
    {
        return [
            'headers' => ['Authorization' => 'Bearer ' . $this->config->getToken()],
            // Don't follow location headers. We want those.
            'allow_redirects' => false,
        ];
    }

    /**
     * Create new batch.
     *
     * @param string $id
     *   Id of batch snapshot
     * @param string $history
     *   History of the snapshot, newline separated.
     *
     * @return string
     *   The ID of the created batch.
     */
    public function createBatch($id, $history = null)
    {
        try {
            $json = ['id' => $id];
            if ($history) {
                $json['history'] = $history;
            }
            $response = $this->client->post('batch', ['json' => $json] + $this->getOptions());
            if ($response->getStatusCode() === 201 && $response->hasHeader('Location')) {
                return $response->getHeader('Location')[0];
            }
            throw new RuntimeException("Unexpected reply to create batch:" . $response->getBody());
        } catch (Exception $e) {
            throw new RuntimeException("Error creating batch:" . $e->getMessage());
        }
    }

    /**
     * Delete batch.
     *
     * @param string $batchId
     *   Id of batch.
     *
     * @return bool
     *   Whether the batch was deleted.
     */
    public function deleteBatch($batchId)
    {
        try {
            $response = $this->client->delete($batchId, $this->getOptions());
            return true;
        } catch (Exception $e) {
            throw new RuntimeException("Error deleting batch:" . $e->getMessage());
        }
        return false;
    }

    /**
     * Submit a checkpoint image.
     *
     * @param string $batchId
     *   Id of batch.
     * @param string $name
     *   Name of checkpoint.
     * @param string $pngData
     *   PNG data.
     *
     * @return bool
     *   Whether the checkpoint was submitted.
     */
    public function checkpoint($batchId, $name, $pngData)
    {
        try {
            $json = [
                'name' => $name,
                'image' => base64_encode($pngData),
            ];
            $response = $this->client->post($batchId . '/checkpoint', ['json' => $json] + $this->getOptions());
            if ($response->getStatusCode() == 200) {
                return true;
            }
        } catch (Exception $e) {
            throw new RuntimeException("Error submitting image:" . $e->getMessage());
        }
        return false;
    }
}
