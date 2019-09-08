<?php

namespace Rorschach\Helpers;

use Symfony\Component\Process\Process;

class Git
{
    protected $git = 'git';

    /**
     * Set the git executable to use.
     *
     * Primarily for testing.
     */
    public function setExecutable($executable)
    {
        $this->git = $executable;
    }

    /**
     * Get history from git.
     *
     * @return string|null
     *   The history, or null if not found or error.
     */
    public function getHistory()
    {
        try {
            $process = new Process([$this->git, 'rev-list', 'HEAD']);
            $process->mustRun();
            return $process->getOutput();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
