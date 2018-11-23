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
     * @param string $sha
     *   The commit SHA.
     */
    public function __construct(WebDriver $webDriver, Client $client, $sha)
    {
        $this->webDriver = $webDriver;
        $this->client = $client;
        $this->batchId = $this->client->createBatch($sha);
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

    public function snapshot($name)
    {
        $pngData = $this->webDriver->takeScreenshot();
        return $this->client->snapshot($this->batchId, $name, $pngData);
    }
}
