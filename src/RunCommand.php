<?php

namespace Rorschach;

use Applitools\BatchInfo;
use Applitools\RectangleSize;
use Applitools\Selenium\Eyes;
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
     * Applitools Eyes.
     * @var Eyes
     */
    protected $eyes;

    /**
     * Webdriver factory.
     * @var WebdriverFactory
     */
    protected $webdriverFactory;

    public function __construct(Config $config, Eyes $eyes, WebdriverFactory $webdriverFactory)
    {
        $this->config = $config;
        $this->eyes = $eyes;
        $this->webdriverFactory = $webdriverFactory;
    }

    /**
     * Invoke the run command.
     *
     * @throws RuntimeException
     *   In case of error.
     */
    public function __invoke($webdriver = null, $baseUrl = null)
    {
        // Eyes automatically uses this env var, but we'll set it explicitly.
        $this->eyes->setApiKey($this->config->getApplitoolsApiKey());
        $batch = new BatchInfo(null);
        $batch->setId($this->config->getApplitoolsBatchId());
        $this->eyes->setBatch($batch);

        $webdriver = $webdriver ?: $this->config->getWebdriverUrl();
        $baseUrl = $baseUrl ?: $this->config->getBaseUrl();
        $baseUrl = rtrim($baseUrl, '/');

        if (empty($webdriver)) {
            throw new \RuntimeException(self::MISSING_WEBDRIVER_URL);
        }

        if (empty($baseUrl)) {
            throw new \RuntimeException(self::MISSING_BASE_URL);
        }

        // Todo: make browser name configurable.
        $webdriver_instance = $this->webdriverFactory->get($webdriver, 'chrome');

        $size = new RectangleSize(
            $this->config->getBrowserWidth(),
            $this->config->getBrowserHeight()
        );
        try {
            $this->eyes->open(
                $webdriver_instance,
                $this->config->getAppName(),
                $this->config->getTestName(),
                $size
            );

            foreach ($this->config->getSteps() as $name => $path) {
                $webdriver_instance->get($baseUrl . $path);
                $this->eyes->checkWindow($name);
            }
        } finally {
            $webdriver_instance->quit();
            // Simply close() without throwing, rather than the documented
            // abortIfNotClosed(). We don't want to exit with an error when
            // validations fail, we'll leave that up to the GitHub
            // integration.
            $this->eyes->close(false);

        }
    }
}
