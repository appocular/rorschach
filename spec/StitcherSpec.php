<?php

namespace spec\Rorschach;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use PhpSpec\ObjectBehavior;
use Rorschach\Stitcher;
use RuntimeException;

class StitcherSpec extends ObjectBehavior
{
    // Set up the happy-path for stitching a screenshot. Test methods can
    // override the mocks to tests different errors.
    function let(RemoteWebDriver $webdriver)
    {
        // Return the page size.
        $webdriver->executeScript('return Math.max.apply(null, [document.body.clientWidth, document.body.scrollWidth, document.documentElement.scrollWidth, document.documentElement.clientWidth])')->willReturn(800);
        $webdriver->executeScript('return Math.max.apply(null, [document.body.clientHeight, document.body.scrollHeight, document.documentElement.scrollHeight, document.documentElement.clientHeight])')->willReturn(1200);

        // Return the viewport size.
        $webdriver->executeScript('return document.documentElement.clientWidth')->willReturn(640);
        $webdriver->executeScript('return document.documentElement.clientHeight')->willReturn(480);

        $webdriver->executeScript(Stitcher::ANIM_KILL_SCRIPT)->willReturn();

        // These are the expected scroll commands. Note that this is not
        // exactly the positions reported in the next section as the browser
        // will only scroll until it reaches the edge of the page.
        $webdriver->executeScript('window.scrollTo(0, 0)')->willReturn(null);
        $webdriver->executeScript("window.scrollTo(0, 480)")->willReturn(null);
        $webdriver->executeScript("window.scrollTo(0, 960)")->willReturn(null);
        $webdriver->executeScript('window.scrollTo(640, 0)')->willReturn(null);
        $webdriver->executeScript("window.scrollTo(640, 480)")->willReturn(null);
        $webdriver->executeScript("window.scrollTo(640, 960)")->willReturn(null);

        // The reported page offsets after the above scroll commands.
        $webdriver->executeScript("return window.pageXOffset")->willReturn(0, 0, 0, 160, 160, 160);
        $webdriver->executeScript("return window.pageYOffset")->willReturn(0, 480, 720, 0, 480, 720);

        // The screenshots from each position. They're a different order
        // because the files was cut in rows, while the stitcher works in
        // columns.
        $webdriver->takeScreenshot()->willReturn(
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk1.png'),
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk3.png'),
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk5.png'),
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk2.png'),
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk4.png'),
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk6.png')
        );

        $this->beConstructedWith($webdriver);
    }

    /**
     * Test that stitching works by mocking the expected chunks and checking
     * that the returned image matches the original image.
     */
    function it_should_stitch_together_a_full_screenshot(RemoteWebDriver $webdriver)
    {

        $png = $this->getWrappedObject()->stitchScreenshot();
        \file_put_contents('/tmp/rorschach-test.png', $png);

        $referenceImage = __DIR__ . '/../fixtures/stitching/jason-leung-1548529-unsplash.png';

        // Check that the generated screenshot is what's expected. Redirect
        // stderr to stdout so we can get the stderr output.
        expect(`compare 2>&1 -dissimilarity-threshold 1 -fuzz 1 -metric AE $referenceImage /tmp/rorschach-test.png /dev/null`)->toBe('0');
        unlink('/tmp/rorschach-test.png');
    }

    /**
     * Test that errors in getting a screenshot throws an error.
     */
    function it_should_throw_on_screenshot_error(RemoteWebDriver $webdriver)
    {
        $webdriver->takeScreenshot()->willReturn(
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk1.png'),
            null,
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk5.png'),
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk2.png'),
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk4.png'),
            \file_get_contents(__DIR__ . '/../fixtures/stitching/chunk6.png')
        );

        $png = $this->shouldThrow(\RuntimeException::class)->duringStitchScreenshot();
    }

    /**
     * Test that it can hide elements.
     */
    function it_should_hide_elements(RemoteWebDriver $webdriver)
    {
        $webdriver->findElements(WebDriverBy::cssSelector('#the_selector'))
            ->willReturn(['le element', '.video'])->shouldBeCalled();
        $webdriver->executeScript("arguments[0].style.visibility='hidden'", ['le element'])
            ->shouldBeCalled();
        $webdriver->executeScript("arguments[0].style.visibility='hidden'", ['.video'])
            ->shouldBeCalled();

        // No return value to check, so call directly on the object.
        $this->getWrappedObject()->hideElements(['#the_selector']);
    }

    function it_should_catch_errors_in_hide_selector(RemoteWebDriver $webdriver)
    {
        $webdriver->findElements(WebDriverBy::cssSelector('bad selector'))
            ->willThrow(new RuntimeException('oh no'))->shouldBeCalled();

        $exception = new RuntimeException('Error hiding elements with selector "bad selector": oh no');
        $this->shouldThrow($exception)->duringHideElements(['bad selector']);
    }

    function it_should_wait_for_wait_script(RemoteWebDriver $webdriver)
    {
        $webdriver->executeScript('the script')->willReturn(false, false, true)->shouldBeCalled();

        // No return value to check, so call directly on the object.
        $this->getWrappedObject()->waitScript('the script');
    }

    function it_should_catch_errors_in_wait_script(RemoteWebDriver $webdriver)
    {
        $webdriver->executeScript('the script')
            ->willThrow(new RuntimeException('oh no'))->shouldBeCalled();

        $exception = new RuntimeException('Error in wait script: oh no');
        $this->shouldThrow($exception)->duringWaitScript('the script');
    }

    function it_should_allow_for_not_killing_anims(RemoteWebDriver $webdriver)
    {
        $webdriver->executeScript(Stitcher::ANIM_KILL_SCRIPT)->shouldNotBeCalled();
        $png = $this->getWrappedObject()->stitchScreenshot(0, true);
    }
}
