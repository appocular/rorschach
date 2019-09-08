<?php

namespace Rorschach\Helpers;

use Rorschach\Step;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads and validates configuration file.
 */
class ConfigFile
{
    const FILE_NAME = 'rorschach.yml';
    const MISSING_CONFIG_FILE_ERROR = 'Could not find ' . self::FILE_NAME .  ' file.';
    const NO_STEPS_ERROR = 'No steps defined in ' . self::FILE_NAME . '.';
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
     * Pages to verify.
     *
     * Hash of name => path
     * @var array
     */
    protected $steps = [];

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

        $defaults = !empty($config['defaults']) ? $config['defaults'] : [];

        // Add in a default browser size.
        $defaults += [
            'browser_height' => self::DEFAULT_BROWSER_HEIGHT,
            'browser_width' => self::DEFAULT_BROWSER_WIDTH,
        ];
        if (!empty($config['steps'])) {
            foreach ($config['steps'] as $name => $step) {
                $this->steps[] = new Step($name, $step, $defaults);
            }
        } else {
            throw new \RuntimeException(self::NO_STEPS_ERROR);
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

    public function getSteps()
    {
        return $this->steps;
    }

    public function getWebdriverUrl()
    {
        return $this->webdriverUrl;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
