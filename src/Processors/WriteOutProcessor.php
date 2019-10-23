<?php

namespace Rorschach\Processors;

use Rorschach\CheckpointProcessor;
use Rorschach\Config;
use Rorschach\Checkpoint;

class WriteOutProcessor implements CheckpointProcessor
{
    protected $path;

    public function __construct(Config $config)
    {
        $this->path = $config->getWriteOut();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Checkpoint $checkpoint, string $pngData): void
    {
        \file_put_contents($this->path . '/' . \urlencode($checkpoint->name) . '.png', $pngData);
    }

    /**
     * {@inheritdoc}
     */
    public function end(): void
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function summarize(): void
    {
        return;
    }
}
