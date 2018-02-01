<?php

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\AdministratorRepository;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityContext extends RawMinkContext
{
    use KernelDictionary;

    /**
     * @When I am logged as :email
     */
    public function iAmLoggedAs(string $email): void
    {
        if (!$user = $this->getAdherentRepository()->findOneBy(['emailAddress' => $email])) {
            throw new \Exception(sprintf('Adherent %s not found', $email));
        }

        $this->logAs($user);
    }

    /**
     * @When I am logged as :email admin
     */
    public function iAmLoggedAsAdmin(string $email): void
    {
        if (!$user = $this->getAdministratorRepository()->loadUserByUsername($email)) {
            throw new \Exception(sprintf('Admin %s not found', $email));
        }

        $this->logAs($user);
    }

    private function logAs(UserInterface $user): void
    {
        $driver = $this->getSession()->getDriver();
        $session = $this->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main_context', $user->getRoles());
        $session->set('_security_main_context', serialize($token));

        $session->save();
        if (!$driver instanceof BrowserKitDriver) {
            throw new \RuntimeException('Unsupported Driver');
        }

        $client = $driver->getClient();
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        $this->getSession()->setCookie($session->getName(), $session->getId());
    }

    protected function getAdherentRepository(): AdherentRepository
    {
        return $this->getContainer()->get('doctrine')->getRepository(Adherent::class);
    }

    protected function getAdministratorRepository(): AdministratorRepository
    {
        return $this->getContainer()->get('doctrine')->getRepository(Administrator::class);
    }
}
