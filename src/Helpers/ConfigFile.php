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
}
