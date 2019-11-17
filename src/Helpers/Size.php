<?php

declare(strict_types=1);

namespace Rorschach\Helpers;

class Size
{
    /**
     * Pixel width.
     *
     * @var int
     */
    public $width;

    /**
     * Pixel height.
     *
     * @var int
     */
    public $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function __toString(): string
    {
        return $this->width . 'x' . $this->height;
    }
}
