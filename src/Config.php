<?php

namespace Rorschach;

use Rorschach\Exceptions\RorschachError;
use Rorschach\Helpers\ConfigFile;
use Rorschach\Helpers\Env;
use Rorschach\Helpers\Git;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Handles configuration and environment.
 */
class Config
{
    /**
     * Snapshot ID (git SHA in our case).
     * @var string
     */
    private $sha;

    /**
     * Appocular token.
     * @var string
     */
    private $token;

    /**
     * Snapshot history.
     * @var string
     */
    private $history;

    /**
     * @var \Rorschach\ConfigFile
     */
    private $configFile;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    public const MISSING_SHA_ERROR = "Please ensure that the GITHUB_SHA or CIRCLE_SHA1 env variable contains the commit SHA.";
    public const MISSING_TOKEN_ERROR = "Please ensure that the APPOCULAR_TOKEN env variable contains the token for Appocular.";
    public const MISSING_WEBDRIVER_URL = 'Please provide a webdriver url, either in the config file or with the --webdriver option.';
    public const MISSING_BASE_URL = 'Please provide a base url, either in the config file or with the --base-url option.';

    public function __construct(Env $env, ConfigFile $configFile, InputInterface $input, Git $git)
    {
        try {
            $this->sha = $env->get('GITHUB_SHA', self::MISSING_SHA_ERROR);
        } catch (RorschachError $e) {
            $this->sha = $env->get('CIRCLE_SHA1', self::MISSING_SHA_ERROR);
        }
        if (empty($this->sha)) {
            throw new RorschachError(self::MISSING_SHA_ERROR);
        }

        $this->token = $env->get('APPOCULAR_TOKEN', self::MISSING_TOKEN_ERROR);
        if (empty($this->token)) {
            throw new RorschachError(self::MISSING_TOKEN_ERROR);
        }

        $this->configFile = $configFile;
        $this->history = $env->getOptional('RORSCHACH_HISTORY', null);
        if (is_null($this->history)) {
            $this->history = $git->getHistory();
        }
        $this->input = $input;
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
     * Get pages to validate.
     */
    public function getCheckpoints()
    {
        return $this->configFile->getCheckpoints();
    }

    /**
     * Get Webdriver URL.
     */
    public function getWebdriverUrl()
    {
        $webDriverUrl = $this->input->getOption('webdriver');

        if (empty($webDriverUrl)) {
            $webDriverUrl = $this->configFile->getWebdriverUrl();
        }

        if (empty($webDriverUrl)) {
            throw new RorschachError(self::MISSING_WEBDRIVER_URL);
        }

        return $webDriverUrl;
    }

    public function getBaseUrl()
    {
        $baseUrl = $this->input->getOption('base-url');

        if (empty($baseUrl)) {
            $baseUrl = $this->configFile->getBaseUrl();
        }

        if (empty($baseUrl)) {
            throw new RorschachError(self::MISSING_BASE_URL);
        }

        // Strip trailing slash, we ensure all paths starts with one.
        return rtrim($baseUrl, '/');
    }

    public function getHistory()
    {
        return $this->history;
    }

    public function getWriteOut()
    {
        return $this->input->getOption('write-out');
    }

    public function getReadIn()
    {
        return $this->input->getOption('read-in');
    }

    public function getBase()
    {
        return $this->input->getOption('base') ? $this->input->getOption('base') : 'alpha.appocular.io';
    }

    public function getWorkers()
    {
        // Default to four workers.
        return $this->configFile->getWorkers() ?? 4;
    }

    public function getVariants()
    {
        return $this->configFile->getVariants();
    }
}
