<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Checkpoint;
use Rorschach\Exceptions\RorschachError;
use Rorschach\Variation;

class VariationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('browser_size', ['800x600', '1200x800']);
        $this->shouldHaveType(Variation::class);
    }

    function it_should_throw_on_unknown_variation()
    {
        $this->beConstructedWith('banana', ['800x600', '1200x800']);
        $this->shouldThrow(new RorschachError(sprintf(Variation::UNKNOWN_VARIATION_ERROR, 'banana')))
            ->duringInstantiation();
    }

    function it_should_throw_on_bad_variations()
    {
        $this->beConstructedWith('browser_size', ['800x600' => '1200x800']);
        $this->shouldThrow(new RorschachError(sprintf(Variation::BAD_VARIATIONS_ERROR, 'browser_size')))
            ->duringInstantiation();
    }

    function it_should_throw_on_malformed_variations()
    {
        $this->beConstructedWith('browser_size', ['800x600', 'banana']);
        $this->shouldThrow(new RorschachError(sprintf(Variation::BAD_BROWSER_SIZE_ERROR, 'banana')))
            ->duringInstantiation();
    }

    function it_should_create_browser_size_variations()
    {
        $this->beConstructedWith('browser_size', ['375x667', '800x600', '1200x800']);

        $checkpoints = [
            new Checkpoint('test', '/'),
            new Checkpoint('test 2', '/two'),
            new Checkpoint('test 3', '/three'),
        ];

        $expected = [
            new Checkpoint('test', '/', [
                'browser_size' => '375x667',
                'meta' => ['browser_size' => '375x667']
            ]),
            new Checkpoint('test 2', '/two', [
                'browser_size' => '375x667',
                'meta' => ['browser_size' => '375x667']
            ]),
            new Checkpoint('test 3', '/three', [
                'browser_size' => '375x667',
                'meta' => ['browser_size' => '375x667']
            ]),
            new Checkpoint('test', '/', [
                'browser_size' => '800x600',
                'meta' => ['browser_size' => '800x600']
            ]),
            new Checkpoint('test 2', '/two', [
                'browser_size' => '800x600',
                'meta' => ['browser_size' => '800x600']
            ]),
            new Checkpoint('test 3', '/three', [
                'browser_size' => '800x600',
                'meta' => ['browser_size' => '800x600']
            ]),
            new Checkpoint('test', '/', [
                'browser_size' => '1200x800',
                'meta' => ['browser_size' => '1200x800']
            ]),
            new Checkpoint('test 2', '/two', [
                'browser_size' => '1200x800',
                'meta' => ['browser_size' => '1200x800']
            ]),
            new Checkpoint('test 3', '/three', [
                'browser_size' => '1200x800',
                'meta' => ['browser_size' => '1200x800']
            ]),
        ];
        $this->getVariations($checkpoints)
            ->shouldBeLike($expected);
    }
}
