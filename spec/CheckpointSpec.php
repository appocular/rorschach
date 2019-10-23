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
        $this->beConstructedWith('test', ['path' => 'path', 'hide' => ['name' => 'hidden']], ['hide' => ['another name' => 'default']]);
        $this->name->shouldBe('test');
        $this->path->shouldBe('/path');
        $this->hide->shouldBe(['name' => 'hidden']);
    }

    function it_should_validate_hide()
    {
        $this->beConstructedWith('test', ['path' => 'path']);
        $this->validateHide(['key' => 'value'])
            ->shouldReturn(true);
        $this->validateHide(['key' => 'value', 'key2' => 'value2'])
            ->shouldReturn(true);
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_HIDE_ERROR))
            ->duringValidateHide('banana');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_HIDE_ERROR))
            ->duringValidateHide(['banana' => []]);
    }

    function it_should_validate_browser_width_and_height()
    {
        $this->beConstructedWith('test', ['path' => 'path']);
        $this->validateBrowserHeight('1')
            ->shouldReturn(true);
        $this->validateBrowserHeight('500')
            ->shouldReturn(true);
        $this->validateBrowserHeight('9999')
            ->shouldReturn(true);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
            ->duringValidateBrowserHeight('2 bannaas');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
            ->duringValidateBrowserHeight("0");
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
            ->duringValidateBrowserHeight("-6");
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
            ->duringValidateBrowserHeight("10000");

        $this->validateBrowserWidth('1')
            ->shouldReturn(true);
        $this->validateBrowserWidth('500')
            ->shouldReturn(true);
        $this->validateBrowserWidth('9999')
            ->shouldReturn(true);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
            ->duringValidateBrowserWidth('2 bannaas');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
            ->duringValidateBrowserWidth("0");
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
            ->duringValidateBrowserWidth("-6");
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_BROWSER_SIZE_ERROR))
            ->duringValidateBrowserWidth("10000");
    }

    function it_should_validate_wait()
    {
        $this->beConstructedWith('test', ['path' => 'path']);
        $this->validateWait('0')
            ->shouldReturn(true);
        $this->validateWait('500')
            ->shouldReturn(true);
        $this->validateWait('7200')
            ->shouldReturn(true);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_WAIT_ERROR))
            ->duringValidateWait('2 bannaas');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_WAIT_ERROR))
            ->duringValidateWait("-6");
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_WAIT_ERROR))
            ->duringValidateWait("10000");
    }

    function it_should_validate_stich_delay()
    {
        $this->beConstructedWith('test', ['path' => 'path']);
        $this->validateStitchDelay('0')
            ->shouldReturn(true);
        $this->validateStitchDelay('500')
            ->shouldReturn(true);
        $this->validateStitchDelay('7200')
            ->shouldReturn(true);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_STITCH_DELAY_ERROR))
            ->duringValidateStitchDelay('2 bannaas');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_STITCH_DELAY_ERROR))
            ->duringValidateStitchDelay("-6");
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_STITCH_DELAY_ERROR))
            ->duringValidateStitchDelay("10000");
    }

    function it_should_validate_wait_script()
    {
        $this->beConstructedWith('test', ['path' => 'path']);
        $this->validateWaitScript('')
            ->shouldReturn(true);
        $this->validateWaitScript('banana')
            ->shouldReturn(true);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_WAIT_SCRIPT_ERROR))
            ->duringValidateWaitScript(['2 bannaas']);
    }

    function it_should_validate_dont_kill_animations()
    {
        $this->beConstructedWith('test', ['path' => 'path']);
        $this->validateDontKillAnimations(true)
            ->shouldReturn(true);
        $this->validateDontKillAnimations(false)
            ->shouldReturn(true);

        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_DONT_KILL_ANIMATIONS_ERROR))
            ->duringValidateDontKillAnimations('true');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_DONT_KILL_ANIMATIONS_ERROR))
            ->duringValidateDontKillAnimations('1');
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_DONT_KILL_ANIMATIONS_ERROR))
            ->duringValidateDontKillAnimations(1);
        $this->shouldThrow(new RorschachError(Checkpoint::VALIDATE_DONT_KILL_ANIMATIONS_ERROR))
            ->duringValidateDontKillAnimations([]);
    }
}
