<?php

namespace Rorschach;

interface CheckpointProcessor
{
    /**
     * Process a step image.
     */
    public function process(Step $step, $pngData) : void;

    /**
     * End processing.
     */
    public function end() : void;
}
