<?php

namespace Rorschach;

interface CheckpointFetcher
{
    /**
     * Fetch a step image.
     *
     * @param Step $step
     *   The step to screenshot.
     *
     * @return string
     *   The step screenshot, in PNG format.
     */
    public function fetch(Step $step) : string;

    /**
     * End processing.
     */
    public function end() : void;
}
