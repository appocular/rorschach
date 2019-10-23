<?php

namespace Rorschach\Fetchers;

use Rorschach\CheckpointFetcher;
use Rorschach\Config;
use Rorschach\Checkpoint;

class ReadInFetcher implements CheckpointFetcher
{
    protected $path;

    public function __construct(Config $config)
    {
        $this->path = $config->getReadIn();
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Checkpoint $checkpoint) : string
    {
        return \file_get_contents($this->path . '/' . \urlencode($checkpoint->name) . '.png');
    }

    /**
     * End processing.
     */
    public function end() : void
    {
    }
}
