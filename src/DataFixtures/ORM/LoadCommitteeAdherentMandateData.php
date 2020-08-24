<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCommitteeAdherentMandateData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $committee1 = $this->getReference('committee-1');
        $carl = $this->getReference('adherent-2');
        $lucie = $this->getReference('adherent-4');

        $mandateFemale = new CommitteeAdherentMandate($lucie, Genders::FEMALE, $committee1, new \DateTime('2020-06-06'));
        $this->setReference('committee-mandate-female', $mandateFemale);
        $manager->persist($mandateFemale);

        $mandateMale = new CommitteeAdherentMandate($carl, Genders::MALE, $committee1, new \DateTime('2020-08-08'));
        $this->setReference('committee-mandate-male', $mandateMale);
        $manager->persist($mandateMale);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
