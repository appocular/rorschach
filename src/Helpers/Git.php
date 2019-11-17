<?php

declare(strict_types=1);

namespace Rorschach\Helpers;

use Symfony\Component\Process\Process;
use Throwable;

class Git
{
    /**
     * Git executable to use.
     *
     * @var string
     */
    protected $git = 'git';

    /**
     * Set the git executable to use.
     *
     * Primarily for testing.
     */
    public function setExecutable(string $executable): void
    {
        $this->git = $executable;
    }

    /**
     * Get history from git.
     *
     * @return ?string
     *   The history, or null if not found or error.
     */
    public function getHistory(): ?string
    {
        try {
            $process = new Process([$this->git, 'rev-list', 'HEAD']);
            $process->mustRun();

            return $process->getOutput();
        } catch (Throwable $e) {
            return null;
        }
    }
}
