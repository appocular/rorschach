<?php

namespace Rorschach;

use Rorschach\Helpers\Env;

/**
 * Handles configuration and environment.
 */
class Config
{
    /**
     * Applitool API key.
     * @var string
     */
    private $applitoolsApiKey;

    /**
     * Applitools batch ID.
     * @var string
     */
    private $applitoolsBatchId;

    const MISSING_API_KEY_ERROR = "Please ensure that the APPLITOOLS_API_KEY contains thu Applitools API key.";
    const MISSING_BATCH_ID_ERROR = "Please ensure that the CIRCLE_SHA1 env variable contains the batch ID.";

    public function __construct(Env $env)
    {
        $this->applitoolsApiKey = $env->get('APPLITOOLS_API_KEY', self::MISSING_API_KEY_ERROR);
        if (empty($this->applitoolsApiKey)) {
            throw new \RuntimeException(self::MISSING_API_KEY_ERROR);
        }

        $this->applitoolsBatchId = $env->get('CIRCLE_SHA1', self::MISSING_BATCH_ID_ERROR);
        if (empty($this->applitoolsBatchId)) {
            throw new \RuntimeException(self::MISSING_BATCH_ID_ERROR);
        }
    }

    /**
     * Get Applitools API key.
     */
    public function getApplitoolsApiKey()
    {
        return $this->applitoolsApiKey;
    }

    /**
     * Get Applitools batch ID.
     */
    public function getApplitoolsBatchId()
    {
        return $this->applitoolsBatchId;
    }
}
