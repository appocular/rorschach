<?php

namespace Rorschach\Appocular;

use Rorschach\Appocular\Client;

class Batch
{
    /**
     * @var \Facebook\WebDriver\WebDriver
     */
    protected $webDriver;

    /**
     * @var \Rorschach\Appocular\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $batchId;

    /**
     * Start a new batch.
     *
     * @param Client $client
     *   Appocular Client.
     * @param string $id
     *   The snapshot id.
     * @param string $history
     *   The snapshot history.
     */
    public function __construct(Client $client, $id, $history = null)
    {
        $this->client = $client;
        $this->batchId = $this->client->createBatch($id, $history);
    }

    public function close()
    {
        return $this->client->deleteBatch($this->batchId);
    }

    /**
     * Get id of batch.
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * Submit checkpoint.
     *
     * @param string $name
     *   Checkpoint name.
     * @param string $pngData
     *   PNG image to submit.
     */
    public function checkpoint(string $name, string $pngData)
    {
        return $this->client->checkpoint($this->batchId, $name, $pngData);
    }
}
