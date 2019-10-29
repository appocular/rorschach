<?php

namespace Rorschach;

use Rorschach\Exceptions\RorschachError;

class Checkpoint
{
    public const MISSING_PATH_ERROR = 'No path for checkpoint "%s".';
    public const VALIDATE_HIDE_ERROR = '"hide" should be a mapping of <name>: <CSS selector>';
    public const VALIDATE_REMOVE_ERROR = '"remove" should be a mapping of <name>: <CSS selector>';
    public const VALIDATE_BROWSER_SIZE_ERROR = '"browser_height" and "browser_width" should be ' .
        'a number between 1 and 9999, inclusive.';
    public const VALIDATE_WAIT_ERROR = '"wait" should be a number between 0 and 7200, inclusive.';
    public const VALIDATE_STITCH_DELAY_ERROR = '"stitch_delay" should be a number between 0 and 7200, inclusive.';
    public const VALIDATE_WAIT_SCRIPT_ERROR = '"wait_script" should be a string.';
    public const UNKNOWN_VARIANT_TYPE_ERROR = 'Unknown variant type "%s".';
    public const BAD_BROWSER_SIZE_ERROR = 'Bad browser size "%s", should be <width>x<height>.';
    public const VALIDATE_CSS_ERROR = '"css" should be a mapping of <name>: <CSS>';

    public $name;
    public $path;
    public $hide;
    public $remove;
    public $browserWidth;
    public $browserHeight;
    public $wait;
    public $stitchDelay;
    public $waitScript;
    public $meta;
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

        //$data = array_merge_recursive($data, $defaults);

        $keys =  [
            'hide',
            'remove',
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
                $val = $data[$key];
                // If its an array, merge with defaults, if given.
                if (is_array($val) && isset($defaults[$key])) {
                    $val += $defaults[$key];
                }
            } else {
                $val = $defaults[$key] ?? null;
            }

            if (isset($val)) {
                if (\method_exists($this, 'validate' . $prop)) {
                    $this->{$prop}  = $this->{'validate' . $prop}($val);
                } else {
                    $this->{$prop} = $val;
                }
            }
        }
    }

    public function validateHide($val)
    {
        return $this->validateHash($val, self::VALIDATE_HIDE_ERROR);
    }

    public function validateRemove($val)
    {
        return $this->validateHash($val, self::VALIDATE_REMOVE_ERROR);
    }

    public function validateBrowserHeight($val)
    {
        return $this->validateInt($val, 1, 9999, self::VALIDATE_BROWSER_SIZE_ERROR);
    }

    public function validateBrowserWidth($val)
    {
        return $this->validateBrowserHeight($val);
    }

    public function validateWait($val)
    {
        return $this->validateInt($val, 0, 7200, self::VALIDATE_WAIT_ERROR);
    }

    public function validateStitchDelay($val)
    {
        return $this->validateInt($val, 0, 7200, self::VALIDATE_STITCH_DELAY_ERROR);
    }

    public function validateWaitScript($val)
    {
        if (is_string($val)) {
            return $val;
        }
        throw new RorschachError(self::VALIDATE_WAIT_SCRIPT_ERROR);
    }

    public function validateCss($val)
    {
        return $this->validateHash($val, self::VALIDATE_CSS_ERROR);
    }

    protected function validateInt($val, $min, $max, $error)
    {
        // is_numeric allows for floats and scientific notation, so we'll just
        // use a regexp.
        if (preg_match('{^\d+$}', $val) && intval($val) >= $min && \intval($val) <= $max) {
            return $val;
        }
        throw new RorschachError($error);
    }

    protected function validateHash($val, $error)
    {
        if (is_array($val)) {
            foreach ($val as $key => $value) {
                if (!\is_string($key) || (!\is_string($value) && !\is_null($value))) {
                    throw new RorschachError($error);
                }

                // Remove null values. This allows for unsetting using ~ in
                // the YAML file.
                if (\is_null($value)) {
                    unset($val[$key]);
                }
            }

            return $val;
        }

        throw new RorschachError($error);
    }
}
