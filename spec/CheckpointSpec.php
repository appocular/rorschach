<?php

namespace spec\Rorschach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Checkpoint;
use Rorschach\Exceptions\RorschachError;

class CheckpointSpec extends ObjectBehavior
{
    function it_should_require_a_path()
    {
        $this->beConstructedWith('test', []);
        $this->shouldThrow(new RorschachError(sprintf(Checkpoint::MISSING_PATH_ERROR, 'test')))->duringInstantiation();
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
        $this->beConstructedWith('test', 'path', ['hide' => ['name' => 'tohide']]);
        $this->name->shouldBe('test');
        $this->path->shouldBe('/path');
        $this->hide->shouldBe(['name' => 'tohide']);
    }

    function it_should_let_values_override_defaults()
    {
        $this->beConstructedWith('test', [
            'path' => 'path',
            'wait' => 10
        ], [
            'wait' => 20
        ]);
        $this->name->shouldBe('test');
        $this->path->shouldBe('/path');
        $this->wait->shouldBe(10);
    }

    function it_should_let_hash_values_override_defaults_selectively()
    {
        $this->beConstructedWith('test', [
            'path' => 'path',
            'hide' => [
                'name' => 'hidden',
                'nulled' => null,
            ]
        ], [
            'hide' => [
                'name' => 'nat hidden',
                'another name' => 'default',
                'nulled' => 'value'
            ]
        ]);
        $this->name->shouldBe('test');
        $this->path->shouldBe('/path');
        $this->hide->shouldBe(['name' => 'hidden', 'another name' => 'default']);
    }

    function it_should_validate_hide()
    {
        $this->beConstructedWith('test', ['path' => 'path']);

        $this->validateHide(['key' => 'value'])
            ->shouldReturn(['key' => 'value']);
        $this->validateHide(['key' => 'value', 'key2' => 'value2'])
            ->shouldReturn(['key' => 'value', 'key2' => 'value2']);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_HIDE_ERROR))
            ->duringValidateHide('banana');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_HIDE_ERROR))
            ->duringValidateHide(['banana' => []]);
    }

    function it_should_validate_remove()
    {
        $this->beConstructedWith('test', ['path' => 'path']);

        $this->validateRemove(['key' => 'value'])
            ->shouldReturn(['key' => 'value']);
        $this->validateRemove(['key' => 'value', 'key2' => 'value2'])
            ->shouldReturn(['key' => 'value', 'key2' => 'value2']);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_REMOVE_ERROR))
            ->duringValidateRemove('banana');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_REMOVE_ERROR))
            ->duringValidateRemove(['banana' => []]);
    }

    function it_should_validate_browser_width_and_height()
    {
        $this->beConstructedWith('test', ['path' => 'path']);

        $goodValues = ['1', '500', '9999'];
        foreach ($goodValues as $val) {
            $this->validateBrowserHeight($val)
                ->shouldReturn($val);
            $this->validateBrowserWidth($val)
                ->shouldReturn($val);
        }

        $badValues = ['2 bannaas', '0', '-6', '10000'];
        foreach ($badValues as $val) {
            $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
                ->duringValidateBrowserHeight($val);
            $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
                ->duringValidateBrowserWidth($val);
        }
    }

    function it_should_validate_wait()
    {
        $this->beConstructedWith('test', ['path' => 'path']);

        $goodValues = ['0', '500', '7200'];
        foreach ($goodValues as $val) {
            $this->validateWait($val)
                ->shouldReturn($val);
        }

        $badValues = ['2 bannaas', '-6', '10000'];
        foreach ($badValues as $val) {
            $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_WAIT_ERROR))
                ->duringValidateWait($val);
        }
    }

    function it_should_validate_stich_delay()
    {
        $this->beConstructedWith('test', ['path' => 'path']);

        $goodValues = ['0', '500', '7200'];
        foreach ($goodValues as $val) {
            $this->validateStitchDelay($val)
                ->shouldReturn($val);
        }

        $badValues = ['2 bannaas', '-6', '10000'];
        foreach ($badValues as $val) {
            $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_STITCH_DELAY_ERROR))
                ->duringValidateStitchDelay($val);
        }
    }

    function it_should_validate_wait_script()
    {
        $this->beConstructedWith('test', ['path' => 'path']);
        $this->validateWaitScript('')
            ->shouldReturn('');
        $this->validateWaitScript('banana')
            ->shouldReturn('banana');

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_WAIT_SCRIPT_ERROR))
            ->duringValidateWaitScript(['2 bannaas']);
    }

    function it_should_validate_css()
    {
        $this->beConstructedWith('test', ['path' => 'path']);

        $this->validateCss(['key' => 'value'])
            ->shouldReturn(['key' => 'value']);
        $this->validateCss(['key' => 'value', 'key2' => 'value2'])
            ->shouldReturn(['key' => 'value', 'key2' => 'value2']);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_CSS_ERROR))
            ->duringValidateCss('banana');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_CSS_ERROR))
            ->duringValidateCss(['banana' => []]);
    }
}