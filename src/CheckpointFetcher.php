<?php

namespace Rorschach;

interface CheckpointFetcher
{
    /**
     * Fetch a checkpoint image.
     *
     * @param Checkpoint $checkpoint
     *   The checkpoint to screenshot.
     *
     * @return string
     *   The checkpoint screenshot, in PNG format.
     */
    public function fetch(Checkpoint $checkpoint) : string;

    /**
     * End processing.
     */
    public function end() : void;
}
