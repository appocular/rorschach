<?php

namespace Rorschach;

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
     * @param string $sha
     *   SHA of the commit of the batch.
     * @param string $history
     *   History of the commit, newline separated.
     *
     * @return Batch
     *   Batch object.
     */
    public function startBatch($sha, $history = null)
    {
        return new Batch($this->client, $sha, $history);
    }
}
