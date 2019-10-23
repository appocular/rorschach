<?php

namespace spec\Rorschach\Processors;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Config;
use Rorschach\Checkpoint;

class WriteOutProcessorSpec extends ObjectBehavior
{
    /**
     * Test that it writes out images.
     */
    function it_should_write_out_images(
        Config $config
    ) {
        $dir = sys_get_temp_dir() . '/rorschach-test-' . getmypid();
        mkdir($dir);
        $config->getWriteOut()->willReturn($dir);
        $this->beConstructedWith($config);

        $this->getWrappedObject()->process(new Checkpoint('Test', '/path'), 'data');
        expect(\file_exists($dir . '/Test.png'))->toBe(true);
        expect(\file_get_contents($dir . '/Test.png'))->toBe('data');
        `rm -rf $dir`;
    }

    /**
     * Test that it urlencodes filenames.
     */
    function it_should_urlencode_filenames(
        Config $config
    ) {
        $dir = sys_get_temp_dir() . '/rorschach-test-' . getmypid();
        mkdir($dir);
        $config->getWriteOut()->willReturn($dir);
        $this->beConstructedWith($config);

        $this->getWrappedObject()->process(new Checkpoint('Name with / funny + chars &', '/path'), 'data');
        expect(\file_exists($dir . '/Name+with+%2F+funny+%2B+chars+%26.png'))->toBe(true);
        expect(\file_get_contents($dir . '/Name+with+%2F+funny+%2B+chars+%26.png'))->toBe('data');
        `rm -rf $dir`;
    }
}
