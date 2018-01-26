<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\PostAddress;
use AppBundle\Membership\AdherentFactory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const USER_1_UUID = '313bd28f-efc8-57c9-8ab7-2106c8be9699';

    public function load(ObjectManager $manager)
    {
        $adherentFactory = $this->getAdherentFactory();

        // Create adherent users list
        $user1 = $adherentFactory->createFromArray([
            'uuid' => self::USER_1_UUID,
            'password' => 'enmarche',
            'email' => 'simple-user@example.ch',
            'gender' => 'female',
            'first_name' => 'Simple',
            'last_name' => 'User',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '32 Zeppelinstrasse', 47.3950786, 8.5361402),
            'birthdate' => '1972-11-24',
            'isAdherent' => false,
        ]);

        $manager->persist($user1);
        $manager->flush();
    }

    private function getAdherentFactory(): AdherentFactory
    {
        return $this->container->get('app.membership.adherent_factory');
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
