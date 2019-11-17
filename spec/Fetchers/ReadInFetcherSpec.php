<?php

namespace spec\Rorschach\Fetchers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rorschach\Config;
use Rorschach\Checkpoint;

class ReadInFetcherSpec extends ObjectBehavior
{
    /**
     * Test that it writes out images.
     */
    function it_should_read_in_images(
        Config $config
    ) {
        $dir = sys_get_temp_dir() . '/rorschach-test-' . getmypid();
        mkdir($dir);
        \file_put_contents($dir . '/Testnull.png', 'image data');
        $config->getReadIn()->willReturn($dir);
        $this->beConstructedWith($config);

        $this->fetch(new Checkpoint('Test', '/path'))->shouldReturn('image data');
        `rm -rf $dir`;
    }

    /**
     * Test that it urlencodes filenames.
     */
    function it_should_read_urlencoded_filenames(
        Config $config
    ) {
        $dir = sys_get_temp_dir() . '/rorschach-test-' . getmypid();
        mkdir($dir);
        \file_put_contents($dir . '/Name+with+%2F+funny+%2B+chars+%26null.png', 'image data');
        $config->getReadIn()->willReturn($dir);
        $this->beConstructedWith($config);

        $this->fetch(new Checkpoint('Name with / funny + chars &', '/path'))->shouldReturn('image data');
        `rm -rf $dir`;
    }
}
