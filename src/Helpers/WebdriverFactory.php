<?php

namespace Rorschach\Helpers;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;

class WebdriverFactory
{
    const WEBDRIVER_CONNECTION_ERROR = 'Could not connect to webdriver on "%s": %s';

    /**
     * Get Webdriver.
     *
     * @param string $address
     *   The address of the webdriver server.
     * @param string $browser
     *   The browser to request.
     *
     * @return RemoteWebDriver
     *   The instantiated Webdriver.
     *
     * @throws \RuntimeException
     *   In case of connection errors.
     */
    public function get($address, $browser)
    {
        try {
            $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => $browser);
            $webdriver = RemoteWebDriver::create($address, $capabilities);
            return $webdriver;
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(self::WEBDRIVER_CONNECTION_ERROR, $address, $e->getMessage()));
        }
    }
}
