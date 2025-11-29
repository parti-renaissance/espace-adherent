<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Repository\AdherentRepository;
use App\Repository\AdministratorRepository;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityContext extends RawMinkContext
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly AdministratorRepository $administratorRepository,
    ) {
    }

    /**
     * @When I am logged as :email
     */
    public function iAmLoggedAs(string $email): void
    {
        if (!$user = $this->adherentRepository->findOneBy(['emailAddress' => $email])) {
            throw new \Exception(\sprintf('Adherent %s not found', $email));
        }

        $this->loginAs($user);
    }

    /**
     * @When I am logged as :email admin
     */
    public function iAmLoggedAsAdmin(string $email): void
    {
        if (!$user = $this->administratorRepository->loadUserByIdentifier($email)) {
            throw new \Exception(\sprintf('Admin %s not found', $email));
        }

        $this->loginAs($user);
    }

    private function loginAs(UserInterface $user): void
    {
        $driver = $this->getSession()->getDriver();

        if ($driver instanceof Selenium2Driver) {
            $page = $this->getSession()->getPage();

            $this->visitPath('/connexion');
            $page->findField('_username')->setValue($user->getUserIdentifier());
            $page->findField('_password')->setValue(LoadAdherentData::DEFAULT_PASSWORD);
            $loginButton = $page->findButton('Connexion') ?? $page->findButton('Me connecter');
            $loginButton->press();

            return;
        }

        $driver->getClient()->loginUser($user, 'main_context');
    }
}
