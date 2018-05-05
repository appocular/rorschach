<?php

namespace Rorschach;

use Applitools\BatchInfo;
use Applitools\RectangleSize;
use Applitools\Selenium\Eyes;
use Rorschach\Config;
use Rorschach\Helpers\WebdriverFactory;

class RunCommand
{

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
    public function __invoke($seleniumAddress)
    {
        // Eyes automatically uses this env var, but we'll set it explicitly.
        $this->eyes->setApiKey($this->config->getApplitoolsApiKey());
        $batch = new BatchInfo(null);
        $batch->setId($this->config->getApplitoolsBatchId());
        $this->eyes->setBatch($batch);

        // Todo: make browser name configurable.
        $webDriver = $this->webdriverFactory->get($seleniumAddress, 'chrome');

        $size = new RectangleSize(
            $this->config->getBrowserWidth(),
            $this->config->getBrowserHeight()
        );
        try {
            $this->eyes->open(
                $webDriver,
                $this->config->getAppName(),
                $this->config->getTestName(),
                $size
            );

            foreach ($this->config->getSteps() as $name => $path) {
                $webDriver->get($path);
                $this->eyes->checkWindow($name);
            }
        } finally {
            $webDriver->quit();
            // Simply close() without throwing, rather than the documented
            // abortIfNotClosed(). We don't want to exit with an error when
            // validations fail, we'll leave that up to the GitHub
            // integration.
            $this->eyes->close(false);

        }
    }
}
