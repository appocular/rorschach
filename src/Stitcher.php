<?php

namespace Rorschach;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use RuntimeException;
use Throwable;

class Stitcher
{
    /**
     * @var \Facebook\WebDriver\WebDriver
     */
    protected $webdriver;

    /**
     * Create stitcher.
     */
    public function __construct(WebDriver $webdriver)
    {
        $this->webdriver = $webdriver;
    }

    public function stitchScreenshot($stitchDelay = 0)
    {
        $total_width = $this->webdriver->executeScript('return Math.max.apply(null, [document.body.clientWidth, document.body.scrollWidth, document.documentElement.scrollWidth, document.documentElement.clientWidth])');
        $total_height = $this->webdriver->executeScript('return Math.max.apply(null, [document.body.clientHeight, document.body.scrollHeight, document.documentElement.scrollHeight, document.documentElement.clientHeight])');
        $viewport_width = $this->webdriver->executeScript('return document.documentElement.clientWidth');
        $viewport_height = $this->webdriver->executeScript('return document.documentElement.clientHeight');
        $this->webdriver->executeScript('window.scrollTo(0, 0)');

        $full_capture = imagecreatetruecolor($total_width, $total_height);
        $repeat_x = ceil($total_width / $viewport_width);
        $repeat_y = ceil($total_height / $viewport_height);
        for ($x = 0; $x < $repeat_x; $x++) {
            $x_pos = $x * $viewport_width;
            $before_top = -1;
            for ($y = 0; $y < $repeat_y; $y++) {
                $y_pos = $y * $viewport_height;
                $this->webdriver->executeScript("window.scrollTo({$x_pos}, {$y_pos})");
                $scroll_left = $this->webdriver->executeScript("return window.pageXOffset");
                $scroll_top = $this->webdriver->executeScript("return window.pageYOffset");
                // If we haven't moved since last iteration, we're done.
                if ($before_top == $scroll_top) {
                    break;
                }

                if ($stitchDelay) {
                    usleep($stitchDelay * 1000000);
                }

                $imageData = $this->webdriver->takeScreenshot();
                if (!$imageData) {
                    throw new \RuntimeException('Could not save screenshot');
                }
                $tmp_image = imagecreatefromstring($imageData);
                imagecopy(
                    $full_capture,
                    $tmp_image,
                    $scroll_left,
                    $scroll_top,
                    0,
                    0,
                    $viewport_width,
                    $viewport_height
                );
                $before_top = $scroll_top;
            }
        }
        $file = fopen('php://temp', 'r+');
        imagepng($full_capture, $file);
        imagedestroy($full_capture);

        $stat = fstat($file);
        rewind($file);
        $data = fread($file, $stat['size']);
        fclose($file);
        return $data;
    }

    public function hideElements($cssSelectors)
    {
        $selector = implode(', ', $cssSelectors);
        try {
            $elements = $this->webdriver->findElements(WebDriverBy::cssSelector($selector));
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf(
                'Error hiding elements with selector "%s": %s',
                $selector,
                $e->getMessage()
            ));
        }
        foreach ($elements as $element) {
            $this->webdriver->executeScript("arguments[0].style.visibility='hidden'", [$element]);
        }
    }

    public function removeElements($cssSelectors)
    {
        $selector = implode(', ', $cssSelectors);
        try {
            $elements = $this->webdriver->findElements(WebDriverBy::cssSelector($selector));
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf(
                'Error removing elements with selector "%s": %s',
                $selector,
                $e->getMessage()
            ));
        }
        foreach ($elements as $element) {
            $this->webdriver->executeScript("arguments[0].parentNode.removeChild(arguments[0])", [$element]);
        }
    }

    public function waitScript($script)
    {
        try {
            $done = $this->webdriver->executeScript($script);
            while (!$done) {
                $done = $this->webdriver->executeScript($script);
            }
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf(
                'Error in wait script: %s',
                $e->getMessage()
            ));
        }
    }

    public function addCss($css)
    {
        $css = strtr($css, ['"' => '\\"', "\n" => '\\n', '\\' => '\\\\']);
        $cssScript = <<<EOF
var node = document.createElement("style");
node.innerHTML = "$css";
document.body.appendChild(node);
EOF;
        $this->webdriver->executeScript($cssScript);
    }
}
