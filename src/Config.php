<?php

namespace Rorschach;

use Rorschach\Helpers\ConfigFile;
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

    /**
     * @var ConfigFile
     */
    private $configFile;

    const MISSING_API_KEY_ERROR = "Please ensure that the APPLITOOLS_API_KEY contains thu Applitools API key.";
    const MISSING_BATCH_ID_ERROR = "Please ensure that the CIRCLE_SHA1 env variable contains the batch ID.";

    public function __construct(Env $env, ConfigFile $configFile)
    {
        $this->applitoolsApiKey = $env->get('APPLITOOLS_API_KEY', self::MISSING_API_KEY_ERROR);
        if (empty($this->applitoolsApiKey)) {
            throw new \RuntimeException(self::MISSING_API_KEY_ERROR);
        }

        $this->applitoolsBatchId = $env->get('CIRCLE_SHA1', self::MISSING_BATCH_ID_ERROR);
        if (empty($this->applitoolsBatchId)) {
            throw new \RuntimeException(self::MISSING_BATCH_ID_ERROR);
        }

        $this->configFile = $configFile;
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

    /**
     * Get app name.
     */
    public function getAppName()
    {
        return $this->configFile->getAppName();
    }

    /**
     * Get test name.
     */
    public function getTestName()
    {
        return $this->configFile->getTestName();
    }

    /**
     * Get browser height.
     */
    public function getBrowserHeight()
    {
        return $this->configFile->getBrowserHeight();
    }

    /**
     * Get browser width.
     */
    public function getBrowserWidth()
    {
        return $this->configFile->getBrowserWidth();
    }
}
