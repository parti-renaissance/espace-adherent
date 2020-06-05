<?php

namespace App\DataFixtures\ORM;

use App\Entity\MyTeam\DelegatedAccess;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDelegatedAccessData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // full access, no committees or cities restriction
        $delegatedAccess1 = new DelegatedAccess();
        $delegatedAccess1->setDelegated($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess1->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess1->setRole('Collaborateur parlementaire');
        $delegatedAccess1->setType('deputy');
        $delegatedAccess1->setAccesses(DelegatedAccess::ACCESSES);

        $manager->persist($delegatedAccess1);

        // access to 2 tabs, no committees or cities restriction
        $delegatedAccess2 = new DelegatedAccess();
        $delegatedAccess2->setDelegated($this->getReference('adherent-4')); // luciole1989@spambox.fr
        $delegatedAccess2->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess2->setRole('Collaborateur parlementaire');
        $delegatedAccess2->setType('deputy');
        $delegatedAccess2->setAccesses([DelegatedAccess::ACCESS_EVENTS, DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess2);

        // access to 2 tabs, with cities restriction
        $delegatedAccess3 = new DelegatedAccess();
        $delegatedAccess3->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess3->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess3->setRole('Collaborateur parlementaire');
        $delegatedAccess3->setType('deputy');
        $delegatedAccess3->setAccesses([DelegatedAccess::ACCESS_EVENTS, DelegatedAccess::ACCESS_ADHERENTS]);
        $delegatedAccess3->setRestrictedCities([]);

        $manager->persist($delegatedAccess3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
