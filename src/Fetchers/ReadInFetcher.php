<?php

namespace Rorschach\Fetchers;

use Rorschach\CheckpointFetcher;
use Rorschach\Config;
use Rorschach\Step;

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
    public function fetch(Step $step) : string
    {
        return \file_get_contents($this->path . '/' . \urlencode($step->name) . '.png');
    }

    /**
     * End processing.
     */
    public function end() : void
    {
    }
}
