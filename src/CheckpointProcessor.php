<?php

namespace Rorschach;

interface CheckpointProcessor
{
    /**
     * Process a step image.
     *
     * @param Step
     *   The step to process.
     * @param string $pngData
     */
    public function process(Step $step, string $pngData) : void;

    /**
     * End processing.
     *
     * May return output strings.
     *
     * @return array|null
     *   Array of strings to output.
     */
    public function end() : void;
}
