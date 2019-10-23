<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Checkpoint;
use RuntimeException;

class CheckpointSpec extends ObjectBehavior
{
    function it_should_require_a_path()
    {
        $this->beConstructedWith('test', []);
        $this->shouldThrow(new RuntimeException(sprintf(Checkpoint::MISSING_PATH_ERROR, 'test')))->duringInstantiation();
    }

    function it_allows_string_as_path_shorthand()
    {
        $this->beConstructedWith('test', '/path');
        $this->name->shouldBe('test');
        $this->path->shouldBe('/path');
    }

    function it_should_ensure_leading_slashes_on_path()
    {
        $this->beConstructedWith('test', 'path');
        $this->name->shouldBe('test');
        $this->path->shouldBe('/path');
    }

    function it_should_take_defaults()
    {
        $this->beConstructedWith('test', 'path', ['hide' => 'tohide']);
        $this->name->shouldBe('test');
        $this->path->shouldBe('/path');
        $this->hide->shouldBe('tohide');
    }

    function it_should_let_values_override_defaults()
    {
        $this->beConstructedWith('test', ['path' => 'path', 'hide' => 'hidden'], ['hide' => 'default']);
        $this->name->shouldBe('test');
        $this->path->shouldBe('/path');
        $this->hide->shouldBe('hidden');
    }
}
