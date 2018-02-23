<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\PostAddress;
use AppBundle\Membership\AdherentFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData extends Fixture
{
    public const USER_1_UUID = '313bd28f-efc8-57c9-8ab7-2106c8be9699';
    public const USER_2_UUID = '413bd28f-57c9-efc8-8ab7-2106c8be9690';

    public function load(ObjectManager $manager)
    {
        $adherentFactory = $this->getAdherentFactory();

        // Create adherent users list
        $user1 = $adherentFactory->createFromArray([
            'uuid' => self::USER_1_UUID,
            'password' => LoadAdherentData::DEFAULT_PASSWORD,
            'email' => 'simple-user@example.ch',
            'first_name' => 'Simple',
            'last_name' => 'User',
            'address' => PostAddress::createForeignAddress('CH', '8057', null, ''),
            'isAdherent' => false,
        ]);
        $key1 = AdherentActivationToken::generate($user1);
        $user1->activate($key1, '2017-01-25 19:34:02');

        $user2 = $adherentFactory->createFromArray([
            'uuid' => self::USER_2_UUID,
            'password' => LoadAdherentData::DEFAULT_PASSWORD,
            'email' => 'simple-user-not-activated@example.ch',
            'first_name' => 'Simple',
            'last_name' => 'User',
            'address' => PostAddress::createForeignAddress('CH', '8057', null, ''),
            'isAdherent' => false,
        ]);

        $manager->persist($user1);
        $manager->persist($user2);
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
