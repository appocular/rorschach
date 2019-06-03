<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Step;
use RuntimeException;

class StepSpec extends ObjectBehavior
{
    function it_should_require_a_path()
    {
        $this->beConstructedWith('test', []);
        $this->shouldThrow(new RuntimeException(sprintf(Step::MISSING_PATH_ERROR, 'test')))->duringInstantiation();
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

}
