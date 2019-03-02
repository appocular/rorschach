<?php

namespace Rorschach\Appocular;

use Facebook\WebDriver\WebDriver;
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
     * @param WebDriver $webDriver
     *   The WebDriver to use.
     * @param Client $client
     *   Appocular Client.
     * @param string $id
     *   The snapshot id.
     * @param string $history
     *   The snapshot history.
     */
    public function __construct(WebDriver $webDriver, Client $client, $id, $history = null)
    {
        $this->webDriver = $webDriver;
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

    public function checkpoint($name)
    {
        $pngData = $this->webDriver->takeScreenshot();
        return $this->client->checkpoint($this->batchId, $name, $pngData);
    }
}
