<?php

declare(strict_types=1);

namespace Rorschach\Fetchers;

use Rorschach\Checkpoint;
use Rorschach\CheckpointFetcher;
use Rorschach\Config;

class ReadInFetcher implements CheckpointFetcher
{
    /**
     * Path to read images from.
     *
     * @var string
     */
    protected $path;

    public function __construct(Config $config)
    {
        $this->path = $config->getReadIn();
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Checkpoint $checkpoint): string
    {
        return \file_get_contents($this->path . '/' .
                                  \urlencode($checkpoint->name . \json_encode($checkpoint->meta)) . '.png');
    }

    /**
     * End processing.
     */
    public function end(): void
    {
    }
}
