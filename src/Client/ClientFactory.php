<?php

namespace App\Client;

use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\ServerExtension;

class ClientFactory
{
    public static function createSeleniumClient(string $seleniumUrl): Client
    {
        $firefoxOptions = new FirefoxOptions();
        $firefoxOptions->addArguments(['--headless', '--window-size=1200,1100', '--disable-gpu', '--no-sandbox']);

        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);

        $client = Client::createSeleniumClient(
            $seleniumUrl,
            $capabilities
        );

        ServerExtension::registerClient($client);

        return $client;
    }
}
