<?php

namespace Tests\App\Behat\Context;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Repository\AdherentRepository;
use App\Repository\AdministratorRepository;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityContext extends RawMinkContext
{
    //    private SessionInterface $session;
    private AdherentRepository $adherentRepository;
    private AdministratorRepository $administratorRepository;

    public function __construct(
        //        SessionInterface $session,
        AdherentRepository $adherentRepository,
        AdministratorRepository $administratorRepository,
    ) {
        //        $this->session = $session;
        $this->adherentRepository = $adherentRepository;
        $this->administratorRepository = $administratorRepository;
    }

    /**
     * @When I am logged as :email
     */
    public function iAmLoggedAs(string $email): void
    {
        if (!$user = $this->adherentRepository->findOneBy(['emailAddress' => $email])) {
            throw new \Exception(\sprintf('Adherent %s not found', $email));
        }

        $this->loginAs($user, 'main');
    }

    /**
     * @When I am logged as :email admin
     */
    public function iAmLoggedAsAdmin(string $email): void
    {
        if (!$user = $this->administratorRepository->loadUserByIdentifier($email)) {
            throw new \Exception(\sprintf('Admin %s not found', $email));
        }

        $this->loginAs($user, 'admin');
    }

    private function loginAs(UserInterface $user, string $firewallName): void
    {
        $driver = $this->getSession()->getDriver();

        if ($driver instanceof Selenium2Driver) {
            $page = $this->getSession()->getPage();

            $this->visitPath('/connexion');
            $page->findField('_login_email')->setValue($user->getUsername());
            $page->findField('_login_password')->setValue(LoadAdherentData::DEFAULT_PASSWORD);
            $loginButton = $page->findButton('Connexion') ?? $page->findButton('Me connecter');
            $loginButton->press();

            return;
        }

        $token = new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        $this->session->set('_security_main_context', serialize($token));
        $this->session->save();

        $client = $driver->getClient();
        $client->getCookieJar()->set(new Cookie($this->session->getName(), $this->session->getId()));
        $this->getSession()->setCookie($this->session->getName(), $this->session->getId());
    }
}
