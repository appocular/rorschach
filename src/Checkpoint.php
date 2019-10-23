<?php

namespace Rorschach;

use RuntimeException;

class Checkpoint
{
    public const MISSING_PATH_ERROR = 'No path for checkpoint "%s".';

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
            throw new RuntimeException(sprintf(self::MISSING_PATH_ERROR, $name));
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
                $this->{$prop} = $checkpoint[$key];
            }
        }
    }
}
