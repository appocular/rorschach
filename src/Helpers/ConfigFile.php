<?php

namespace Rorschach\Helpers;

use Symfony\Component\Yaml\Yaml;

/**
 * Loads and validates configuration file.
 */
class ConfigFile
{
    const FILE_NAME = 'rorschach.yml';
    const MISSING_CONFIG_FILE_ERROR = 'Could not find ' . self::FILE_NAME .  ' file.';
    const MISSING_APP_NAME_ERROR = 'Please specify app_name in ' . self::FILE_NAME;
    const MISSING_TEST_NAME_ERROR = 'Please specify test_name in ' . self::FILE_NAME;
    const DEFAULT_BROWSER_HEIGHT = 1080;
    const DEFAULT_BROWSER_WIDTH = 1920;
    /**
     * Configured app name.
     * @var string
     */
    protected $appName;

    /**
     * Configured test name.
     * @var string
     */
    protected $testName;

    /**
     * Browser height.
     * @var int
     */
    protected $browserHeight;

    /**
     * Browser width.
     * @var int
     */
    protected $browserWidth;

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

        if (!empty($config['app_name'])) {
            $this->appName = $config['app_name'];
        } else {
            $errors[] = self::MISSING_APP_NAME_ERROR;
        }

        if (!empty($config['test_name'])) {
            $this->testName = $config['test_name'];
        } else {
            $errors[] = self::MISSING_TEST_NAME_ERROR;
        }

        if (!empty($config['webdriver_url'])) {
            $this->webdriverUrl = $config['webdriver_url'];
        }

        if (!empty($config['base_url'])) {
            $this->baseUrl = $config['base_url'];
        }

        if (!empty($config['steps'])) {
            foreach ($config['steps'] as $name => $path) {
                // Ensure all paths starts with a slash.
                $this->steps[$name] = '/' . ltrim($path, '/');
            }
        }

        $this->browserHeight = !empty($config['browser_height']) ?
            $config['browser_height'] : self::DEFAULT_BROWSER_HEIGHT;
        $this->browserWidth = !empty($config['browser_width']) ?
            $config['browser_width'] : self::DEFAULT_BROWSER_WIDTH;

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

    public function getBrowserHeight()
    {
        return $this->browserHeight;
    }

    public function getBrowserWidth()
    {
        return $this->browserWidth;
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
