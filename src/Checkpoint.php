<?php

namespace Rorschach;

use Rorschach\Exceptions\RorschachError;

class Checkpoint
{
    public const MISSING_PATH_ERROR = 'No path for checkpoint "%s".';
    public const VALIDATE_HIDE_ERROR = '"hide" should be a mapping of <name>: <CSS selector>';
    public const VALIDATE_BROWSER_SIZE_ERROR = '"browser_height" and "browser_width" should be ' .
        'a number between 1 and 9999, inclusive.';
    public const VALIDATE_WAIT_ERROR = '"wait" should be a number between 0 and 7200, inclusive.';
    public const VALIDATE_STITCH_DELAY_ERROR = '"stitch_delay" should be a number between 0 and 7200, inclusive.';
    public const VALIDATE_WAIT_SCRIPT_ERROR = '"wait_script" should be a string.';
    public const VALIDATE_DONT_KILL_ANIMATIONS_ERROR = '"dont_kill_animations" should be true/false.';

    public $name;
    public $path;
    public $hide;
    public $browserWidth;
    public $browserHeight;
    public $wait;
    public $stitchDelay;
    public $waitScript;
    public $dontKillAnimations;

    public function __construct(string $name, $checkpoint, $defaults = [])
    {
        $this->name = $name;
        if (is_string($checkpoint)) {
            $checkpoint = ['path' => $checkpoint];
        }

        if (!isset($checkpoint['path'])) {
            throw new RorschachError(sprintf(self::MISSING_PATH_ERROR, $name));
        }
        // Ensure all paths starts with a slash.
        $this->path = '/' . ltrim($checkpoint['path'], '/');

        $checkpoint += $defaults;

        $keys =  [
            'hide',
            'browser_height',
            'browser_width',
            'wait',
            'stitch_delay',
            'wait_script',
            'dont_kill_animations',
        ];
        foreach ($keys as $key) {
            $prop = lcfirst(str_replace('_', '', ucwords($key, '_')));

            if (isset($checkpoint[$key])) {
                if (\method_exists($this, 'validate' . $prop)) {
                    $this->{'validate' . $prop}($checkpoint[$key]);
                }

                $this->{$prop} = $checkpoint[$key];
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
