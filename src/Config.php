<?php

declare(strict_types=1);

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
     *
     * @var string
     */
    private $sha;

    /**
     * Appocular token.
     *
     * @var string
     */
    private $token;

    /**
     * Snapshot history.
     *
     * @var string
     */
    private $history;

    /**
     * Our configuration file.
     *
     * @var \Rorschach\ConfigFile
     */
    private $configFile;

    /**
     * Input, for extra parameters.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    public const MISSING_SHA_ERROR = "Please ensure that the GITHUB_SHA or CIRCLE_SHA1 env " .
        "variable contains the commit SHA.";
    public const MISSING_TOKEN_ERROR = "Please ensure that the APPOCULAR_TOKEN env " .
        "variable contains the token for Appocular.";
    public const MISSING_WEBDRIVER_URL = 'Please provide a webdriver url, either in " .
        "the config file or with the --webdriver option.';
    public const MISSING_BASE_URL = 'Please provide a base url, either in the config " .
        "file or with the --base-url option.';

    public function __construct(Env $env, ConfigFile $configFile, InputInterface $input, Git $git)
    {
        try {
            $this->sha = $env->get('GITHUB_SHA', self::MISSING_SHA_ERROR);
        } catch (RorschachError $e) {
            $this->sha = $env->get('CIRCLE_SHA1', self::MISSING_SHA_ERROR);
        }

        if (!$this->sha) {
            throw new RorschachError(self::MISSING_SHA_ERROR);
        }

        $this->token = $env->get('APPOCULAR_TOKEN', self::MISSING_TOKEN_ERROR);

        if (!$this->token) {
            throw new RorschachError(self::MISSING_TOKEN_ERROR);
        }

        $this->configFile = $configFile;
        $this->history = $env->getOptional('RORSCHACH_HISTORY', null);

        if (\is_null($this->history)) {
            $this->history = $git->getHistory();
        }

        $this->input = $input;
    }

    /**
     * Get commit SHA.
     */
    public function getSha(): string
    {
        return $this->sha;
    }

    /**
     * Get Appocular token.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get pages to validate.
     *
     * @return array<\Rorschach\Checkpoint>
     */
    public function getCheckpoints(): array
    {
        return $this->configFile->getCheckpoints();
    }

    /**
     * Get Webdriver URL.
     */
    public function getWebdriverUrl(): string
    {
        $webDriverUrl = $this->input->getOption('webdriver');

        if (!$webDriverUrl) {
            $webDriverUrl = $this->configFile->getWebdriverUrl();
        }

        if (!$webDriverUrl) {
            throw new RorschachError(self::MISSING_WEBDRIVER_URL);
        }

        return $webDriverUrl;
    }

    public function getBaseUrl(): string
    {
        $baseUrl = $this->input->getOption('base-url');

        if (!$baseUrl) {
            $baseUrl = $this->configFile->getBaseUrl();
        }

        if (!$baseUrl) {
            throw new RorschachError(self::MISSING_BASE_URL);
        }

        // Strip trailing slash, we ensure all paths starts with one.
        return \rtrim($baseUrl, '/');
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function getWriteOut(): ?string
    {
        return $this->input->getOption('write-out');
    }

    public function getReadIn(): ?string
    {
        return $this->input->getOption('read-in');
    }

    public function getBase(): string
    {
        return $this->input->getOption('base') ?: 'alpha.appocular.io';
    }

    public function getWorkers(): int
    {
        // Default to four workers.
        return $this->configFile->getWorkers() ?? 4;
    }

    /**
     * @return array<\Rorschach\Variation>
     */
    public function getVariants(): array
    {
        return $this->configFile->getVariants();
    }
}
