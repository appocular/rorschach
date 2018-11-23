<?php

namespace Rorschach;

use Facebook\WebDriver\WebDriver;
use Rorschach\Appocular\Batch;
use Rorschach\Appocular\Client;

class Appocular
{
    /**
     * @var \Rorschach\Appocular\Client
     */
    protected $client;

    /**
     * Create Appocular instance.
     *
     * @param Client $client
     *   Appocular\Client to use for communication.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Start a new batch.
     *
     * @param WebDriver $webDriver
     *   WebDriver to use.
     * @param string $sha
     *   SHA of the commit of the batch.
     *
     * @return Batch
     *   Batch object.
     */
    public function startBatch($webDriver, $sha)
    {
        return new Batch($webDriver, $this->client, $sha);
    }
}
