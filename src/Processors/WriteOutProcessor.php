<?php

declare(strict_types=1);

namespace Rorschach\Processors;

use Rorschach\Checkpoint;
use Rorschach\CheckpointProcessor;
use Rorschach\Config;

class WriteOutProcessor implements CheckpointProcessor
{
    /**
     * Path to write images to.
     *
     * @var string
     */
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
        \file_put_contents($this->path . '/' .
                           \urlencode($checkpoint->name . \json_encode($checkpoint->meta)) . '.png', $pngData);
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
