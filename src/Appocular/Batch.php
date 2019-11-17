<?php

declare(strict_types=1);

namespace Rorschach\Appocular;

class Batch
{
    /**
     * WebDriver to use.
     *
     * @var \Facebook\WebDriver\WebDriver
     */
    protected $webDriver;

    /**
     * Appocular client.
     *
     * @var \Rorschach\Appocular\Client
     */
    protected $client;

    /**
     * The ID of the batch we're running.
     *
     * @var string
     */
    protected $batchId;

    /**
     * Start a new batch.
     *
     * @param \Rorschach\Appocular\Client $client
     *   Appocular Client.
     * @param string $id
     *   The snapshot id.
     * @param string $history
     *   The snapshot history.
     */
    public function __construct(Client $client, string $id, ?string $history = null)
    {
        $this->client = $client;
        $this->batchId = $this->client->createBatch($id, $history);
    }

    public function close(): bool
    {
        return $this->client->deleteBatch($this->batchId);
    }

    /**
     * Get id of batch.
     */
    public function getBatchId(): string
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
     * @param array<string, string> $meta
     *   Meta data.
     */
    public function checkpoint(string $name, string $pngData, ?array $meta = null): bool
    {
        return $this->client->checkpoint($this->batchId, $name, $pngData, $meta);
    }
}
