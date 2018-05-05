<?php

namespace Rorschach;

use Rorschach\Helpers\Env;

/**
 * Handles configuration and environment.
 */
class Config
{
    const MISSING_API_KEY_ERROR = "Please ensure that the APPLITOOLS_API_KEY contains thu Applitools API key.";
    const MISSING_BATCH_ID_ERROR = "Please ensure that the CIRCLE_SHA1 env variable contains the batch ID.";

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
        $apiKey = $this->env->get('APPLITOOLS_API_KEY', self::MISSING_API_KEY_ERROR);
        if (empty($apiKey)) {
            throw new \RuntimeException(self::MISSING_API_KEY_ERROR);
        }
        return $apiKey;
    }

    /**
     * Get batch ID from env.
     *
     * @throws RuntimeException
     *   If CIRCLE_SHA1 env variable is not set.
     */
    public function getApplitoolsBatchId()
    {
        $batchId = $this->env->get('CIRCLE_SHA1', self::MISSING_BATCH_ID_ERROR);
        if (empty($batchId)) {
            throw new \RuntimeException(self::MISSING_BATCH_ID_ERROR);
        }
        return $batchId;
    }
}
