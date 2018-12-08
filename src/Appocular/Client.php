<?php

namespace Rorschach\Appocular;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use RuntimeException;

class Client
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Construct a new client.
     *
     * @param GuzzleFactory $guzzleFactory
     *   Factory to get Guzzle client from.
     */
    public function __construct(GuzzleFactory $guzzleFactory)
    {
        $this->client = $guzzleFactory->get();
    }

    /**
     * Create new batch.
     *
     * @param string $id
     *   Id of batch snapshot
     *
     * @return string
     *   The ID of the created batch.
     */
    public function createBatch($id)
    {
        try {
            $response = $this->client->post('batch', ['json' => ['id' => $id]]);
            $json = json_decode($response->getBody());
            if (isset($json->id)) {
                return $json->id;
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
            $response = $this->client->delete('batch/' . $batchId);
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
            $response = $this->client->post('batch/' . $batchId . '/checkpoint', ['json' => $json]);
            if ($response->getStatusCode() == 200) {
                return true;
            }
        } catch (Exception $e) {
            throw new RuntimeException("Error submitting image:" . $e->getMessage());
        }
        return false;
    }
}