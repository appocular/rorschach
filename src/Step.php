<?php

namespace Rorschach;

use RuntimeException;

class Step
{
    const MISSING_PATH_ERROR = 'No path for step "%s".';

    public $name;
    public $path;
    public $hide;
    public $browserWidth;
    public $browserHeight;
    public $wait;
    public $stitchDelay;

    public function __construct(string $name, $step, $defaults = [])
    {
        $this->name = $name;
        if (is_string($step)) {
            $step = ['path' => $step];
        }

        if (!isset($step['path'])) {
            throw new RuntimeException(sprintf(self::MISSING_PATH_ERROR, $name));
        }
        // Ensure all paths starts with a slash.
        $this->path = '/' . ltrim($step['path'], '/');

        $step += $defaults;

        foreach (['hide', 'browser_height', 'browser_width', 'wait', 'stitch_delay'] as $key) {
            $prop = lcfirst(str_replace('_', '', ucwords($key, '_')));

            if (isset($step[$key])) {
                $this->{$prop} = $step[$key];
            }
        }
    }
}
