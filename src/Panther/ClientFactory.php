<?php

namespace App\Panther;

use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Symfony\Component\Panther\Client;

class ClientFactory
{
    public static function createSeleniumClient(string $seleniumUrl): Client
    {
        $firefoxOptions = new FirefoxOptions();
        $firefoxOptions->addArguments(['--headless', '--window-size=1200,1100', '--disable-gpu', '--no-sandbox']);

        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);

        return Client::createSeleniumClient($seleniumUrl, $capabilities);
    }
}
