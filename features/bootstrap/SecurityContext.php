<?php

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Repository\AdherentRepository;
use App\Repository\AdministratorRepository;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

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

        $this->loginAs($user, 'main');
    }

    /**
     * @When I am logged as :email admin
     */
    public function iAmLoggedAsAdmin(string $email): void
    {
        if (!$user = $this->getAdministratorRepository()->loadUserByUsername($email)) {
            throw new \Exception(sprintf('Admin %s not found', $email));
        }

        $this->loginAs($user, 'admin');
    }

    private function loginAs(UserInterface $user, string $firewallName): void
    {
        $driver = $this->getSession()->getDriver();
        $session = $this->getContainer()->get('session');

        if ($driver instanceof Selenium2Driver) {
            $page = $this->getSession()->getPage();

            $this->visitPath('/connexion');
            $page->findField('_login_email')->setValue($user->getUsername());
            $page->findField('_login_password')->setValue(LoadAdherentData::DEFAULT_PASSWORD);
            $page->findButton('Connexion')->press();

            return;
        }

        $token = new PostAuthenticationGuardToken($user, $firewallName, $user->getRoles());
        $session->set('_security_main_context', serialize($token));
        $session->save();

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
