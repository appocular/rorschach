<?php

namespace Rorschach;

use Applitools\Selenium\Eyes;
use Applitools\BatchInfo;
use Rorschach\Config;

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

    public function __construct(Config $config, Eyes $eyes)
    {
        $this->config = $config;
        $this->eyes = $eyes;
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
    }
}
