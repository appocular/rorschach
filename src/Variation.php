<?php

namespace Rorschach;

use Rorschach\Exceptions\RorschachError;

class Variation
{
    public const UNKNOWN_VARIATION_ERROR = 'Unknown variation type "%s".';
    public const BAD_VARIATIONS_ERROR = 'Invalid variants for "%s".';
    public const BAD_BROWSER_SIZE_ERROR = 'Bad browser size "%s", should be <width>x<height>.';

    /**
     * Variation name.
     * @var string
     */
    protected $name;

    /**
     * Variants.
     * @var string[]
     */
    protected $variations = [];

    public function __construct($name, $variations)
    {
        if (!in_array($name, ['browser_size'])) {
            throw new RorschachError(sprintf(self::UNKNOWN_VARIATION_ERROR, $name));
        }

        foreach ($variations as $key => $value) {
            if (!is_integer($key) || !is_string($value)) {
                throw new RorschachError(sprintf(self::BAD_VARIATIONS_ERROR, $name));
            }
            if (!preg_match('{^(?<width>\d+)x(?<height>\d+)$}', $value, $matches)) {
                throw new RorschachError(sprintf(self::BAD_BROWSER_SIZE_ERROR, $value));
            }
            $this->variations[$value] = array_intersect_key($matches, ['width' => null, 'height' => null]);
        }

        $this->name = $name;
    }

    public function getVariations($checkpoints)
    {
        $variations = [];
        // Browser size is the only variation currently.
        foreach ($this->variations as $name => $size) {
            foreach ($checkpoints as $checkpoint) {
                $variation = clone $checkpoint;
                $variation->browserWidth = $size['width'];
                $variation->browserHeight = $size['height'];
                $variation->meta = $variation->meta ?? [];
                $variation->meta['browser_size'] = $name;

                $variations[] = $variation;
            }
        }

        return empty($variations) ? $checkpoints : $variations;
    }
}
