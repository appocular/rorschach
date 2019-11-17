<?php

declare(strict_types=1);

namespace Rorschach;

interface CheckpointProcessor
{
    /**
     * Process a checkpoint image.
     *
     * @param Checkpoint
     *   The checkpoint to process.
     * @param string $pngData
     */
    public function process(Checkpoint $checkpoint, string $pngData): void;

    /**
     * End processing.
     */
    public function end(): void;

    /**
     * Summarize run.
     *
     * Is called in the main process.
     */
    public function summarize(): void;
}
