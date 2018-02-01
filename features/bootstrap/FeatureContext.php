<?php

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class FeatureContext extends RawMinkContext
{
    private $tokenManager;

    public function __construct(CsrfTokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @Given I resolved the captcha
     */
    public function iResolvedTheCaptcha()
    {
        $this->getSession()->getPage()->find('css', 'input[name="g-recaptcha-response"]')->setValue('dummy');
    }

    /**
     * @When I fill in hidden field :fieldId with :value
     */
    public function fillField($fieldId, $value)
    {
        $this->getSession()->getPage()->findById($fieldId)->setValue($value);
    }

    /**
     * @Given I send a :method request to :url with :csrf_id token
     */
    public function iPostToAFormWith($method, $url, $csrfId)
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof BrowserKitDriver) {
            throw new \Exception('This step is only supported by the BrowserKitDriver');
        }

        $driver->getClient()->request(
            $method,
            $url,
            ['token' => $this->tokenManager->getToken($csrfId)->getValue()]
        );
    }
}
