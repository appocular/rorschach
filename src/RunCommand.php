<?php

namespace Rorschach;

use Rorschach\Config;

class RunCommand
{

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Invoke the run command.
     *
     * @throws RuntimeException
     *   In case of error.
     */
    public function __invoke($seleniumAddress)
    {
        // Eyes automatically uses this env var, so just test that it's set.
        $this->config->getApplitoolsApiKey();
    }
}
