<?php

declare(strict_types=1);

namespace Rorschach;

use Rorschach\Exceptions\RorschachError;
use Rorschach\Helpers\Size;

class Checkpoint
{
    public const MISSING_PATH_ERROR = 'No path for checkpoint "%s".';
    public const VALIDATE_REMOVE_ERROR = '"remove" should be a mapping of <name>: <CSS selector>';
    public const VALIDATE_BROWSER_SIZE_ERROR = '"browser_size" should be <width>x<height>, ' .
        '<height> and <width> a number between 1 and 9999, inclusive.';
    public const VALIDATE_WAIT_ERROR = '"wait" should be a number between 0 and 7200, inclusive.';
    public const VALIDATE_STITCH_DELAY_ERROR = '"stitch_delay" should be a number between 0 and 7200, inclusive.';
    public const VALIDATE_WAIT_SCRIPT_ERROR = '"wait_script" should be a string.';
    public const VALIDATE_CSS_ERROR = '"css" should be a mapping of <name>: <CSS>';

    /**
     * Name of checkpoint.
     *
     * @var string
     */
    public $name;

    /**
     * Path to checkpoint.
     *
     * @var string
     */
    public $path;

    /**
     * Element selectors to remove from page before screenshot.
     *
     * @var array<string, string>
     */
    public $remove;

    /**
     * Size to screenshot at.
     *
     * @var \Rorschach\Helpers\Size
     */
    public $browserSize;

    /**
     * Seconds to wait after loading the page.
     *
     * @var float
     */
    public $wait;

    /**
     * Seconds to wait between screenshots when stitching.
     *
     * @var float
     */
    public $stitchDelay;

    /**
     * JavaScript to use for waiting.
     *
     * @var string
     */
    public $waitScript;

    /**
     * Metadata for checkpoint.
     *
     * @var array<string, string>
     */
    public $meta;

    /**
     * CSS to apply to page before screenshot.
     *
     * @var array<string, string>
     */
    public $css;

    /**
     * @param string|array<string, string|array> $data
     * @param string|array<string, string|array> $defaults
     */
    public function __construct(string $name, $data, array $defaults = [])
    {
        $this->name = $name;

        if (\is_string($data)) {
            $data = ['path' => $data];
        }

        if (!isset($data['path'])) {
            throw new RorschachError(\sprintf(self::MISSING_PATH_ERROR, $name));
        }

        // Ensure all paths starts with a slash.
        $this->path = '/' . \ltrim($data['path'], '/');

        $keys =  [
            'remove',
            'browser_size',
            'browser_height',
            'browser_width',
            'wait',
            'stitch_delay',
            'wait_script',
            'meta',
            'css',
        ];

        foreach ($keys as $key) {
            $prop = \lcfirst(\str_replace('_', '', \ucwords($key, '_')));

            if (isset($data[$key])) {
                $value = $data[$key];
                // If its an array, merge with defaults, if given.

                if (\is_array($value) && isset($defaults[$key])) {
                    $value += $defaults[$key];
                }
            } else {
                $value = $defaults[$key] ?? null;
            }

            if (!isset($value)) {
                continue;
            }

            $this->{$prop} = \method_exists($this, 'validate' . $prop) ?
                                 $this->{'validate' . $prop}($value) : $value;
        }
    }

    /**
     * @return array<string, string>
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function validateRemove($value): array
    {
        return $this->validateHash($value, self::VALIDATE_REMOVE_ERROR);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function validateBrowserSize($value): Size
    {
        if (!\is_string($value) || !\preg_match('{^(?<width>\d+)x(?<height>\d+)$}', $value, $matches)) {
            throw new RorschachError(\sprintf(self::VALIDATE_BROWSER_SIZE_ERROR, $value));
        }

        return new Size(
            $this->validateInt($matches['width'], 1, 9999, self::VALIDATE_BROWSER_SIZE_ERROR),
            $this->validateInt($matches['height'], 1, 9999, self::VALIDATE_BROWSER_SIZE_ERROR),
        );
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function validateWait($value): float
    {
        return $this->validateFloat($value, 0, 7200, self::VALIDATE_WAIT_ERROR);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function validateStitchDelay($value): float
    {
        return $this->validateFloat($value, 0, 7200, self::VALIDATE_STITCH_DELAY_ERROR);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function validateWaitScript($value): string
    {
        if (\is_string($value)) {
            return $value;
        }

        throw new RorschachError(self::VALIDATE_WAIT_SCRIPT_ERROR);
    }

    /**
     * @return array<string, string>
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function validateCss($value): array
    {
        return $this->validateHash($value, self::VALIDATE_CSS_ERROR);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    protected function validateInt($value, int $min, int $max, string $error): int
    {
        if (\is_string($value) && \preg_match('{^\d+$}', $value)) {
            $value = \intval($value);
        }

        if (!\is_int($value)) {
            throw new RorschachError($error);
        }

        if ($value >= $min && $value <= $max) {
            return $value;
        }

        throw new RorschachError($error);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    protected function validateFloat($value, int $min, int $max, string $error): float
    {
        if (\is_string($value) && \preg_match('{^\d+(\.\d+)?$}', $value)) {
            $value = \floatval($value);
        }

        if (!\is_float($value) && !\is_int($value)) {
            throw new RorschachError($error);
        }

        if ($value >= $min && $value <= $max) {
            return $value;
        }

        throw new RorschachError($error);
    }

    /**
     * @return array<string, string>
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    protected function validateHash($hash, string $error): array
    {
        if (!\is_array($hash)) {
            throw new RorschachError($error);
        }

        foreach ($hash as $key => $value) {
            if (!\is_string($key)) {
                throw new RorschachError($error);
            }

            // Remove null values. This allows for unsetting using ~ in
            // the YAML file.
            if (\is_null($value)) {
                unset($hash[$key]);

                continue;
            }

            if (!\is_string($value)) {
                throw new RorschachError($error);
            }
        }

        return $hash;
    }
}
