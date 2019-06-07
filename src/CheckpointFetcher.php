<?php

namespace Rorschach;

interface CheckpointFetcher
{
    /**
     * Fetch a step image.
     */
    public function fetch(Step $step) : string;

    /**
     * End processing.
     */
    public function end() : void;
}
