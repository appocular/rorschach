<?php

namespace spec\Rorschach\Helpers;

use Rorschach\Helpers\Git;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GitSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Git::class);
    }

    /**
     * Test that it returns history from the git command.
     */
    function it_returns_history_from_git()
    {
        $this->getWrappedObject()->setExecutable(__DIR__ . '/../../fixtures/git/git');

        $this->getHistory()->shouldReturn("sha1\nsha2\nsha3\nsha4\n");
    }

    /**
     * Test that it returns no history in the case of error.
     */
    function it_returns_no_history_when_git_fails()
    {
        $this->getWrappedObject()->setExecutable(__DIR__ . '/../../fixtures/git/failing-git');

        $this->getHistory()->shouldReturn(null);
    }

    /**
     * Test that it returns no history if git is not found.
     */
    function it_returns_no_history_when_git_not_found()
    {
        $this->getWrappedObject()->setExecutable(__DIR__ . '/../../fixtures/git/no-git');

        $this->getHistory()->shouldReturn(null);
    }
}
