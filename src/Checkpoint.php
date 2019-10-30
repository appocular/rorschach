<?php

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
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $path;

    /**
     * @var array
     */
    public $remove;

    /**
     * @var \Rorschach\Helpers\Size
     */
    public $browserSize;
    public $browserWidth;
    public $browserHeight;

    /**
     * @var float
     */
    public $wait;

    /**
     * @var float
     */
    public $stitchDelay;

    /**
     * @var string
     */
    public $waitScript;

    /**
     * @var array
     */
    public $meta;

    /**
     * @var array
     */
    public $css;

    public function __construct(string $name, $data, $defaults = [])
    {
        $this->name = $name;
        if (is_string($data)) {
            $data = ['path' => $data];
        }

        if (!isset($data['path'])) {
            throw new RorschachError(sprintf(self::MISSING_PATH_ERROR, $name));
        }
        // Ensure all paths starts with a slash.
        $this->path = '/' . ltrim($data['path'], '/');

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
            $prop = lcfirst(str_replace('_', '', ucwords($key, '_')));

            if (isset($data[$key])) {
                $value = $data[$key];
                // If its an array, merge with defaults, if given.
                if (is_array($value) && isset($defaults[$key])) {
                    $value += $defaults[$key];
                }
            } else {
                $value = $defaults[$key] ?? null;
            }

            if (isset($value)) {
                if (\method_exists($this, 'validate' . $prop)) {
                    $this->{$prop}  = $this->{'validate' . $prop}($value);
                } else {
                    $this->{$prop} = $value;
                }
            }
        }
    }

    public function validateRemove($value)
    {
        return $this->validateHash($value, self::VALIDATE_REMOVE_ERROR);
    }

    public function validateBrowserSize($value)
    {
        if (!preg_match('{^(?<width>\d+)x(?<height>\d+)$}', $value, $matches)) {
            throw new RorschachError(sprintf(self::VALIDATE_BROWSER_SIZE_ERROR, $value));
        }

        return new Size(
            $this->validateInt($matches['width'], 1, 9999, self::VALIDATE_BROWSER_SIZE_ERROR),
            $this->validateInt($matches['height'], 1, 9999, self::VALIDATE_BROWSER_SIZE_ERROR)
        );
    }

    public function validateWait($value)
    {
        return $this->validateFloat($value, 0, 7200, self::VALIDATE_WAIT_ERROR);
    }

    public function validateStitchDelay($value)
    {
        return $this->validateFloat($value, 0, 7200, self::VALIDATE_STITCH_DELAY_ERROR);
    }

    public function validateWaitScript($value)
    {
        if (is_string($value)) {
            return $value;
        }
        throw new RorschachError(self::VALIDATE_WAIT_SCRIPT_ERROR);
    }

    public function validateCss($value)
    {
        return $this->validateHash($value, self::VALIDATE_CSS_ERROR);
    }

    protected function validateInt($value, $min, $max, $error)
    {
        // is_numeric allows for floats and scientific notation, so we'll just
        // use a regexp.
        if (preg_match('{^\d+$}', $value) && intval($value) >= $min && \intval($value) <= $max) {
            return \intval($value);
        }
        throw new RorschachError($error);
    }

    protected function validateFloat($value, $min, $max, $error)
    {
        // is_numeric scientific notation, so we'll just use a regexp.
        if (preg_match('{^\d+(\.\d+)?$}', $value) && \floatval($value) >= $min && \floatval($value) <= $max) {
            return \floatval($value);
        }
        throw new RorschachError($error);
    }

    protected function validateHash($hash, $error)
    {
        if (is_array($hash)) {
            foreach ($hash as $key => $value) {
                if (!\is_string($key) || (!\is_string($value) && !\is_null($value))) {
                    throw new RorschachError($error);
                }

                // Remove null values. This allows for unsetting using ~ in
                // the YAML file.
                if (\is_null($value)) {
                    unset($hash[$key]);
                }
            }

            return $hash;
        }

        throw new RorschachError($error);
    }
}
