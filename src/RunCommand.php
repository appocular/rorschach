<?php

namespace Rorschach;

use Rorschach\Config;
use Rorschach\Helpers\WebdriverFactory;

class RunCommand
{
    const MISSING_WEBDRIVER_URL = 'Please provide a webdriver url, either in the config file or with the --webdriver option.';
    const MISSING_BASE_URL = 'Please provide a base url, either in the config file or with the --base-url option.';

    /**
     * Configuration.
     * @var Config
     */
    protected $config;

    /**
     * Webdriver factory.
     * @var WebdriverFactory
     */
    protected $webdriverFactory;

    public function __construct(Config $config, WebdriverFactory $webdriverFactory)
    {
        $this->config = $config;
        $this->webdriverFactory = $webdriverFactory;
    }

    /**
     * Invoke the run command.
     *
     * @throws RuntimeException
     *   In case of error.
     */
    public function __invoke(Appocular $appocular, $webdriver = null, $baseUrl = null)
    {
        $webdriver = $webdriver ?: $this->config->getWebdriverUrl();
        $baseUrl = $baseUrl ?: $this->config->getBaseUrl();
        // Strip trailing slash, we ensure all paths starts with one.
        $baseUrl = rtrim($baseUrl, '/');

        if (empty($webdriver)) {
            throw new \RuntimeException(self::MISSING_WEBDRIVER_URL);
        }

        if (empty($baseUrl)) {
            throw new \RuntimeException(self::MISSING_BASE_URL);
        }

        $webdriverInstance = $this->webdriverFactory->get($webdriver, 'chrome');

        $batch = null;
        try {
            // todo: get branch name and repo...
            $batch = $appocular->startBatch($this->config->getSha(), $this->config->getHistory());

            foreach ($this->config->getSteps() as $name => $path) {
                $webdriverInstance->get($baseUrl . $path);
                $batch->checkpoint($name, $webdriverInstance->takeScreenshot());
            }
        } finally {
            $webdriverInstance->quit();
            if ($batch) {
                $batch->close();
            }
        }
    }
}
