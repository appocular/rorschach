<?php

declare(strict_types=1);

namespace Rorschach\Helpers;

use Exception;
use Rorschach\Checkpoint;
use Rorschach\Exceptions\RorschachError;
use Rorschach\Variation;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads and validates configuration file.
 */
class ConfigFile
{
    public const FILE_NAME = 'rorschach.yml';
    public const MISSING_CONFIG_FILE_ERROR = 'Could not find ' . self::FILE_NAME .  ' file.';
    public const EMPTY_CONFIG_FILE_ERROR = self::FILE_NAME .  ' is empty.';
    public const NO_CHECKPOINTS_ERROR = 'No checkpoints defined in ' . self::FILE_NAME . '.';
    public const BAD_VARIANTS_ERROR = 'Variations data should be a list ' . self::FILE_NAME . '.';
    public const DEFAULT_BROWSER_HEIGHT = 1080;
    public const DEFAULT_BROWSER_WIDTH = 1920;

    /**
     * Webdriver url.
     *
     * @var string|null
     */
    protected $webdriverUrl;

    /**
     * Base url.
     *
     * @var string|null
     */
    protected $baseUrl;

    /**
     * Number of workers.
     *
     * @var int|null
     */
    protected $workers;

    /**
     * Pages to verify.
     *
     * Hash of name => path.
     *
     * @var array<\Rorschach\Checkpoint>
     */
    protected $checkpoints = [];

    /**
     * Variants of checkpoints.
     *
     * Hash of name => variations.
     *
     * @var array<\Rorschach\Variation>
     */
    protected $variants = [];

    public function __construct()
    {
        $dir = \getcwd();

        while (!\file_exists($dir . '/' . self::FILE_NAME) && $dir !== '/') {
            $dir = \dirname($dir);
        }

        if (!\file_exists($dir . '/' . self::FILE_NAME)) {
            throw new RorschachError(self::MISSING_CONFIG_FILE_ERROR);
        }

        try {
            $config = Yaml::parseFile($dir . '/' . self::FILE_NAME);
        } catch (Exception $e) {
            throw new RorschachError('Error parsing ' . self::FILE_NAME . ': ' . $e->getMessage());
        }

        if (!\is_array($config) || !$config) {
            throw new RorschachError(self::EMPTY_CONFIG_FILE_ERROR);
        }

        if (\array_key_exists('webdriver_url', $config) && $config['webdriver_url']) {
            $this->webdriverUrl = $config['webdriver_url'];
        }

        if (\array_key_exists('base_url', $config) && $config['base_url']) {
            $this->baseUrl = $config['base_url'];
        }

        if (\array_key_exists('workers', $config) && $config['workers']) {
            $this->workers = (int) $config['workers'];
        }

        $defaults = \array_key_exists('defaults', $config) && $config['defaults'] ? $config['defaults'] : [];

        // Add in a default browser size.
        $defaults += [
            'browser_size' => self::DEFAULT_BROWSER_WIDTH . 'x' . self::DEFAULT_BROWSER_HEIGHT,
        ];

        if (!\array_key_exists('checkpoints', $config) || !$config['checkpoints']) {
            throw new RorschachError(self::NO_CHECKPOINTS_ERROR);
        }

        foreach ($config['checkpoints'] as $name => $checkpoint) {
            $this->checkpoints[] = new Checkpoint($name, $checkpoint, $defaults);
        }

        if (!\array_key_exists('variations', $config) || !$config['variations']) {
            return;
        }

        foreach ($config['variations'] as $name => $variants) {
            if (!\is_array($variants)) {
                throw new RorschachError(self::BAD_VARIANTS_ERROR);
            }

            $this->variants[] = new Variation($name, $variants);
        }
    }

    /**
     * @return array<\Rorschach\Checkpoint>
     */
    public function getCheckpoints(): array
    {
        return $this->checkpoints;
    }

    public function getWebdriverUrl(): ?string
    {
        return $this->webdriverUrl;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function getWorkers(): ?int
    {
        return $this->workers;
    }

    /**
     * @return array<\Rorschach\Variation>
     */
    public function getVariants(): array
    {
        return $this->variants;
    }
}
