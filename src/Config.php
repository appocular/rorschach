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
     * Commit SHA.
     * @var string
     */
    private $sha;

    /**
     * Appocular token.
     * @var string
     */
    private $token;

    /**
     * Commit history.
     * @var string
     */
    private $history;

    /**
     * @var ConfigFile
     */
    private $configFile;

    const MISSING_SHA_ERROR = "Please ensure that the CIRCLE_SHA1 env variable contains the commit SHA.";
    const MISSING_TOKEN_ERROR = "Please ensure that the APPOCULAR_TOKEN env variable contains the token for Appocular.";

    public function __construct(Env $env, ConfigFile $configFile)
    {
        $this->sha = $env->get('CIRCLE_SHA1', self::MISSING_SHA_ERROR);
        if (empty($this->sha)) {
            throw new \RuntimeException(self::MISSING_SHA_ERROR);
        }

        $this->token = $env->get('APPOCULAR_TOKEN', self::MISSING_TOKEN_ERROR);
        if (empty($this->token)) {
            throw new \RuntimeException(self::MISSING_TOKEN_ERROR);
        }

        $this->configFile = $configFile;
        $this->history = $env->getOptional('RORSCHACH_HISTORY', null);
    }

    /**
     * Get commit SHA.
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * Get Appocular token.
     */
    public function getToken()
    {
        return $this->token;
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

    /**
     * Get pages to validate.
     */
    public function getSteps()
    {
        return $this->configFile->getSteps();
    }

    /**
     * Get Webdriver URL.
     */
    public function getWebdriverUrl()
    {
        return $this->configFile->getWebdriverUrl();
    }

    public function getBaseUrl()
    {
        return $this->configFile->getBaseUrl();
    }

    public function getHistory()
    {
        return $this->history;
    }
}
