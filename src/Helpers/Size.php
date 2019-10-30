<?php

namespace Rorschach\Helpers;

class Size
{
    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function __toString()
    {
        return $this->width . 'x' . $this->height;
    }
}
