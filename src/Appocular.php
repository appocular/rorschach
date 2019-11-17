<?php

declare(strict_types=1);

namespace Rorschach;

use Rorschach\Appocular\Batch;
use Rorschach\Appocular\Client;

class Appocular
{
    /**
     * Client to use.
     *
     * @var \Rorschach\Appocular\Client
     */
    protected $client;

    /**
     * Create Appocular instance.
     *
     * @param \Rorschach\Appocular\Client $client
     *   Appocular\Client to use for communication.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Start a new batch.
     *
     * @param string $id
     *   Snapshot ID.
     * @param string $history
     *   History of the commit, newline separated.
     */
    public function startBatch(string $id, ?string $history = null): Batch
    {
        return new Batch($this->client, $id, $history);
    }
}
