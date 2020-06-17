<?php

namespace App\DataFixtures\ORM;

use App\Entity\MyTeam\DelegatedAccess;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDelegatedAccessData extends Fixture
{
    private const ACCESS_UUID_1 = '2e80d106-4bcb-4b28-97c9-3856fc235b27';
    private const ACCESS_UUID_2 = 'f4ce89da-1272-4a01-a47e-4ce5248ce018';
    private const ACCESS_UUID_3 = '96076afb-2243-4251-97fe-8201d50c3256';
    private const ACCESS_UUID_4 = '411faa64-202d-4ff2-91ce-c98b29af28ef';
    private const ACCESS_UUID_5 = 'd2315289-a3fd-419c-a3dd-3e1ff71b754d';

    public function load(ObjectManager $manager)
    {
        // full access, no committees or cities restriction
        $delegatedAccess1 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_1));
        $delegatedAccess1->setDelegated($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess1->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess1->setRole('Collaborateur parlementaire');
        $delegatedAccess1->setType('deputy');
        $delegatedAccess1->setAccesses(DelegatedAccess::ACCESSES);

        $manager->persist($delegatedAccess1);

        // access to 2 tabs, no committees or cities restriction
        $delegatedAccess2 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_2));
        $delegatedAccess2->setDelegated($this->getReference('adherent-4')); // luciole1989@spambox.fr
        $delegatedAccess2->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess2->setRole('Collaborateur parlementaire');
        $delegatedAccess2->setType('deputy');
        $delegatedAccess2->setAccesses([DelegatedAccess::ACCESS_EVENTS, DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess2);

        // access to 2 tabs, with cities restriction
        $delegatedAccess3 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_3));
        $delegatedAccess3->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess3->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess3->setRole('Collaborateur parlementaire');
        $delegatedAccess3->setType('deputy');
        $delegatedAccess3->setAccesses([DelegatedAccess::ACCESS_MESSAGES, DelegatedAccess::ACCESS_EVENTS]);
        $delegatedAccess3->setRestrictedCities(['59360', '59350', '59044', '59002']);

        $manager->persist($delegatedAccess3);

        // second access to same user, but of type senator, with different accesses
        $delegatedAccess4 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_4));
        $delegatedAccess4->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess4->setDelegator($this->getReference('senator-59')); // senateur@en-marche-dev.fr
        $delegatedAccess4->setRole('Collaborateur parlementaire');
        $delegatedAccess4->setType('senator');
        $delegatedAccess4->setAccesses([DelegatedAccess::ACCESS_MESSAGES, DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess4);

        // second deputy access to same user, with different accesses
        $delegatedAccess4 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_5));
        $delegatedAccess4->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess4->setDelegator($this->getReference('deputy-75-2')); // deputy-75-2@en-marche-dev.fr
        $delegatedAccess4->setRole('Collaborateur parlementaire');
        $delegatedAccess4->setType('deputy');
        $delegatedAccess4->setAccesses([DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess4);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
