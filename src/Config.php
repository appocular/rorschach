<?php

namespace Rorschach;

use Rorschach\Helpers\Env;

/**
 * Handles configuration and environment.
 */
class Config
{
    const MISSING_API_KEY_ERROR = "Please ensure that the APPLITOOLS_API_KEY env is set.";

    public function __construct(Env $env)
    {
        $this->env = $env;
    }

    /**
     * Get Applitools key from env.
     *
     * @throws RuntimeException
     *   If APPLITOOLS_API_KEY env variable is not set.
     */
    public function getApplitoolsApiKey()
    {
        return $this->env->get('APPLITOOLS_API_KEY', self::MISSING_API_KEY_ERROR);
    }
}
