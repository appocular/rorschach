<?php

declare(strict_types=1);

namespace spec\Rorschach\Processors;

use PhpSpec\ObjectBehavior;
use Rorschach\Checkpoint;
use Rorschach\Config;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// phpcs:disable Squiz.Scope.MethodScope.Missing
// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
class WriteOutProcessorSpec extends ObjectBehavior
{
    /**
     * Test that it writes out images.
     */
    function it_should_write_out_images(
        Config $config
    ) {
        $dir = \sys_get_temp_dir() . '/rorschach-test-' . \getmypid();
        \mkdir($dir);
        $config->getWriteOut()->willReturn($dir);
        $this->beConstructedWith($config);

        $this->getWrappedObject()->process(new Checkpoint('Test', '/path'), 'data');
        \expect(\file_exists($dir . '/Testnull.png'))->toBe(true);
        \expect(\file_get_contents($dir . '/Testnull.png'))->toBe('data');
        `rm -rf $dir`;
    }

    /**
     * Test that it urlencodes filenames.
     */
    function it_should_urlencode_filenames(
        Config $config
    ) {
        $dir = \sys_get_temp_dir() . '/rorschach-test-' . \getmypid();
        \mkdir($dir);
        $config->getWriteOut()->willReturn($dir);
        $this->beConstructedWith($config);

        $this->getWrappedObject()->process(new Checkpoint('Name with / funny + chars &', '/path'), 'data');
        \expect(\file_exists($dir . '/Name+with+%2F+funny+%2B+chars+%26null.png'))
            ->toBe(true);
        \expect(\file_get_contents($dir . '/Name+with+%2F+funny+%2B+chars+%26null.png'))
            ->toBe('data');
        `rm -rf $dir`;
    }
}
