<?php

namespace Rorschach\Helpers;

use Rorschach\Checkpoint;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads and validates configuration file.
 */
class ConfigFile
{
    const FILE_NAME = 'rorschach.yml';
    const MISSING_CONFIG_FILE_ERROR = 'Could not find ' . self::FILE_NAME .  ' file.';
    const NO_CHECKPOINTS_ERROR = 'No checkpoints defined in ' . self::FILE_NAME . '.';
    const DEFAULT_BROWSER_HEIGHT = 1080;
    const DEFAULT_BROWSER_WIDTH = 1920;

    /**
     * Webdriver url.
     * @var string|null
     */
    protected $webdriverUrl;

    /**
     * Base url.
     * @var string|null
     */
    protected $baseUrl;

    /**
     * Number of workers.
     * @var int|null
     */
    protected $workers;

    /**
     * Pages to verify.
     *
     * Hash of name => path
     * @var array
     */
    protected $checkpoints = [];

    public function __construct()
    {
        $dir = getcwd();
        while (!file_exists($dir . '/' . self::FILE_NAME) && $dir != '/') {
            $dir = dirname($dir);
        }
        if (!file_exists($dir . '/' . self::FILE_NAME)) {
            throw new \RuntimeException(self::MISSING_CONFIG_FILE_ERROR);
        }

        $errors = [];
        try {
            $config = YAML::parseFile($dir . '/' . self::FILE_NAME);
        } catch (\Exception $e) {
            $errors[] = 'Error parsing ' . self::FILE_NAME . ': ' . $e->getMessage();
        }

        if (!empty($config['webdriver_url'])) {
            $this->webdriverUrl = $config['webdriver_url'];
        }

        if (!empty($config['base_url'])) {
            $this->baseUrl = $config['base_url'];
        }

        if (!empty($config['workers'])) {
            $this->workers = (int) $config['workers'];
        }

        $defaults = !empty($config['defaults']) ? $config['defaults'] : [];

        // Add in a default browser size.
        $defaults += [
            'browser_height' => self::DEFAULT_BROWSER_HEIGHT,
            'browser_width' => self::DEFAULT_BROWSER_WIDTH,
        ];
        if (!empty($config['checkpoints'])) {
            foreach ($config['checkpoints'] as $name => $checkpoint) {
                $this->checkpoints[] = new Checkpoint($name, $checkpoint, $defaults);
            }
        } else {
            throw new \RuntimeException(self::NO_CHECKPOINTS_ERROR);
        }

        if (!empty($errors)) {
            throw new \RuntimeException(implode('\n', $errors));
        }
    }

    public function getAppName()
    {
        return $this->appName;
    }

    public function getTestName()
    {
        return $this->testName;
    }

    public function getCheckpoints()
    {
        return $this->checkpoints;
    }

    public function getWebdriverUrl()
    {
        return $this->webdriverUrl;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getWorkers()
    {
        return $this->workers;
    }
}
