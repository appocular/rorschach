<?php

declare(strict_types=1);

namespace Rorschach;

use Rorschach\Exceptions\RorschachError;

class Variation
{
    public const UNKNOWN_VARIATION_ERROR = 'Unknown variation type "%s".';
    public const BAD_VARIATIONS_ERROR = 'Invalid variants for "%s".';
    public const BAD_BROWSER_SIZE_ERROR = 'Bad browser size "%s", should be <width>x<height>.';

    /**
     * Variation name.
     *
     * @var string
     */
    protected $name;

    /**
     * Variants.
     *
     * @var array<string>
     */
    protected $variations = [];

    /**
     * @param array<string, array<string>> $variations
     */
    public function __construct(string $name, array $variations)
    {
        if (!\in_array($name, ['browser_size'])) {
            throw new RorschachError(\sprintf(self::UNKNOWN_VARIATION_ERROR, $name));
        }

        foreach ($variations as $key => $value) {
            if (!\is_string($value)) {
                throw new RorschachError(\sprintf(self::BAD_VARIATIONS_ERROR, $name));
            }

            if (!\preg_match('{^(?<width>\d+)x(?<height>\d+)$}', $value, $matches)) {
                throw new RorschachError(\sprintf(self::BAD_BROWSER_SIZE_ERROR, $value));
            }

            if (\is_integer($key)) {
                $key = $value;
            }

            $this->variations[$key] = $value;
        }

        $this->name = $name;
    }

    /**
     * @param array<\Rorschach\Checkpoint> $checkpoints
     *
     * @return array<\Rorschach\Checkpoint>
     */
    public function getVariations(array $checkpoints): array
    {
        $variations = [];

        // Browser size is the only variation currently.
        foreach ($this->variations as $name => $size) {
            foreach ($checkpoints as $checkpoint) {
                $variation = clone $checkpoint;
                $variation->browserSize = $variation->validateBrowserSize($size);
                $variation->meta = $variation->meta ?? [];
                $variation->meta['browser_size'] = $name;

                $variations[] = $variation;
            }
        }

        return $variations ?: $checkpoints;
    }
}
