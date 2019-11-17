<?php

declare(strict_types=1);

namespace Rorschach\Appocular;

use Exception;
use GuzzleHttp\Client as Guzzle;
use Rorschach\Config;
use RuntimeException;

class Client
{

    /**
     * HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The configuration.
     *
     * @var \Rorschach\Config
     */
    protected $config;

    /**
     * Construct a new client.
     *
     * @param \GuzzleHttp\Client $client
     *   Guzzle client.
     */
    public function __construct(Guzzle $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Get the request options with authorization header.
     *
     * @return array<array|bool, string>
     */
    protected function getOptions(): array
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
    public function createBatch(string $id, ?string $history = null): string
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
    public function deleteBatch(string $batchId): bool
    {
        try {
            $this->client->delete($batchId, $this->getOptions());

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
     * @param array<string, string> $meta
     *   Meta data.
     *
     * @return bool
     *   Whether the checkpoint was submitted.
     */
    public function checkpoint(string $batchId, string $name, string $pngData, ?array $meta = null): bool
    {
        try {
            $json = [
                'name' => $name,
                'image' => \base64_encode($pngData),
            ];

            if ($meta) {
                $json['meta'] = $meta;
            }

            $response = $this->client->post($batchId . '/checkpoint', ['json' => $json] + $this->getOptions());

            if ($response->getStatusCode() === 201) {
                return true;
            }
        } catch (Exception $e) {
            throw new RuntimeException("Error submitting image:" . $e->getMessage());
        }

        throw new RuntimeException("Error submitting image, unknown response, code " .
                                   $response->getStatusCode() . ', body: ' . $response->getBody());
    }
}
