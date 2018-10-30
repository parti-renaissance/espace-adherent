<?php

use Webmozart\Assert\Assert;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Mink\Driver\Selenium2Driver;
use AppBundle\Entity\Adherent;

class DoctrineContext extends RawMinkContext
{
    use KernelDictionary;

    private $user;

    /**
     * @Then I should have :field field equal to :value
     */
    public function iShouldHaveFieldEqualTo($field, $value)
    {
        $adherent = $this->getCurrentUser();
        Assert::notNull($adherent, 'no adherent logged');

        $function = (($value == "false" || $value == "true") ? "is" : "get") . ucfirst($field);
        Assert::true(method_exists($adherent, $function), sprintf('Method %s does not exists', $function));

        Assert::eq($value, $adherent->$function(), sprintf('%s != %s', $value, $adherent->$function()));
    }

    private function getCurrentUser(): ?Adherent
    {
        if (!$this->user) {
            $session = $this->getContainer()->get('session');
            Assert::notNull($session, 'Session is null');

            $securityMainContext = $session->get('_security_main_context');
            Assert::notNull($securityMainContext, 'securityMainContext is null');

            $usernamePasswordToken = unserialize($securityMainContext);
            Assert::notNull($usernamePasswordToken, 'usernamePasswordToken is null');

            $this->user = $usernamePasswordToken->getUser();
        }

        return $this->user;
    }
}
