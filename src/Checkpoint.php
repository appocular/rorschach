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
    public const VALIDATE_DONT_KILL_ANIMATIONS_ERROR = '"dont_kill_animations" should be true/false.';
    public const UNKNOWN_VARIANT_TYPE_ERROR = 'Unknown variant type "%s".';
    public const BAD_BROWSER_SIZE_ERROR = 'Bad browser size "%s", should be <width>x<height>.';

    public $name;
    public $path;
    public $hide;
    public $remove;
    public $browserWidth;
    public $browserHeight;
    public $wait;
    public $stitchDelay;
    public $waitScript;
    public $dontKillAnimations;
    public $meta;

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

        $data += $defaults;

        $keys =  [
            'hide',
            'remove',
            'browser_height',
            'browser_width',
            'wait',
            'stitch_delay',
            'wait_script',
            'dont_kill_animations',
            'meta',
        ];
        foreach ($keys as $key) {
            $prop = lcfirst(str_replace('_', '', ucwords($key, '_')));

            if (isset($data[$key])) {
                if (\method_exists($this, 'validate' . $prop)) {
                    $this->{'validate' . $prop}($data[$key]);
                }

                $this->{$prop} = $data[$key];
            }
        }
    }

    public function validateHide($val)
    {
        if (is_array($val)) {
            foreach ($val as $key => $val) {
                if (!\is_string($key) || !\is_string($val)) {
                    throw new RorschachError(self::VALIDATE_HIDE_ERROR);
                }
            }

            return true;
        }

        throw new RorschachError(self::VALIDATE_HIDE_ERROR);
    }

    public function validateRemove($val)
    {
        if (is_array($val)) {
            foreach ($val as $key => $val) {
                if (!\is_string($key) || !\is_string($val)) {
                    throw new RorschachError(self::VALIDATE_REMOVE_ERROR);
                }
            }

            return true;
        }

        throw new RorschachError(self::VALIDATE_REMOVE_ERROR);
    }

    public function validateBrowserHeight($val)
    {
        if ($this->validateInt($val, 1, 9999)) {
            return true;
        }
        throw new RorschachError(self::VALIDATE_BROWSER_SIZE_ERROR);
    }

    public function validateBrowserWidth($val)
    {
        return $this->validateBrowserHeight($val);
    }

    public function validateWait($val)
    {
        if ($this->validateInt($val, 0, 7200)) {
            return true;
        }
        throw new RorschachError(self::VALIDATE_WAIT_ERROR);
    }

    public function validateStitchDelay($val)
    {
        if ($this->validateInt($val, 0, 7200)) {
            return true;
        }
        throw new RorschachError(self::VALIDATE_STITCH_DELAY_ERROR);
    }

    public function validateWaitScript($val)
    {
        if (is_string($val)) {
            return true;
        }
        throw new RorschachError(self::VALIDATE_WAIT_SCRIPT_ERROR);
    }

    public function validateDontKillAnimations($val)
    {
        if (is_bool($val)) {
            return true;
        }
        throw new RorschachError(self::VALIDATE_DONT_KILL_ANIMATIONS_ERROR);
    }

    protected function validateInt($val, $min, $max)
    {
        // is_numeric allows for floats and scientific notation, so we'll just
        // use a regexp.
        if (preg_match('{^\d+$}', $val) && intval($val) >= $min && \intval($val) <= $max) {
            return true;
        }
        return false;
    }
}
