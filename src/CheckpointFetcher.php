<?php

declare(strict_types=1);

namespace Rorschach;

interface CheckpointFetcher
{
    /**
     * Fetch a checkpoint image.
     *
     * @param \Rorschach\Checkpoint $checkpoint
     *   The checkpoint to screenshot.
     *
     * @return string
     *   The checkpoint screenshot, in PNG format.
     */
    public function fetch(Checkpoint $checkpoint): string;

    /**
     * End processing.
     */
    public function end(): void;
}
