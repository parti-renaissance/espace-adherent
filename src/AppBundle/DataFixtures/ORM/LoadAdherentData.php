<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Membership\AdherentFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadAdherentData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->getAdherentFactory();

        $adherent1 = $factory->createFromArray([
            'password' => 'secret!12345',
            'email' => 'michelle.dufour@example.ch',
            'gender' => 'female',
            'first_name' => 'Michelle',
            'last_name' => 'Dufour',
            'country' => 'CH',
            'birthdate' => '1972-11-23',
        ]);

        $adherent2 = $factory->createFromArray([
            'password' => 'secret!12345',
            'email' => 'carl999@example.fr',
            'gender' => 'male',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
            'country' => 'FR',
            'address' => '122 rue de Mouxy',
            'city' => '73100-73182',
            'postal_code' => '73100',
            'birthdate' => '1950-07-08',
            'position' => 'retired',
            'phone' => '33 0111223344',
        ]);

        $key1 = AdherentActivationToken::generate($adherent1);
        $key2 = AdherentActivationToken::generate($adherent2);

        $adherent2->activate($key2);

        $resetPasswordToken = AdherentResetPasswordToken::generate($adherent1);

        $manager->persist($key1);
        $manager->persist($key2);
        $manager->persist($adherent1);
        $manager->persist($adherent2);
        $manager->persist($resetPasswordToken);
        $manager->flush();
    }

    private function getAdherentFactory(): AdherentFactory
    {
        return $this->container->get('app.membership.adherent_factory');
    }
}
