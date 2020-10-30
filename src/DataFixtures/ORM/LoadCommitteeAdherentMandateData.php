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
        $committee3 = $this->getReference('committee-3');
        $carl = $this->getReference('adherent-2');
        $gisele = $this->getReference('adherent-5');
        $jacques = $this->getReference('adherent-3');
        $lucie = $this->getReference('adherent-4');

        $mandateFemale1 = new CommitteeAdherentMandate($gisele, Genders::FEMALE, $committee1, new \DateTime('2020-06-06'));
        $this->setReference('committee-mandate-female', $mandateFemale1);
        $manager->persist($mandateFemale1);

        $mandateMale1 = new CommitteeAdherentMandate($carl, Genders::MALE, $committee1, new \DateTime('2020-08-08'));
        $this->setReference('committee-mandate-male', $mandateMale1);
        $manager->persist($mandateMale1);

        $mandateMale2 = new CommitteeAdherentMandate($jacques, Genders::MALE, $committee1, new \DateTime('2018-01-01'), new \DateTime('2019-12-31'));
        $this->setReference('committee-mandate-male', $mandateMale2);
        $manager->persist($mandateMale2);

        $mandateFemale2 = new CommitteeAdherentMandate($lucie, Genders::FEMALE, $committee3, new \DateTime('2020-06-06'));
        $this->setReference('committee-mandate-female', $mandateFemale2);
        $manager->persist($mandateFemale2);

        $mandateMale3 = new CommitteeAdherentMandate($jacques, Genders::MALE, $committee3, new \DateTime('2020-08-08'));
        $this->setReference('committee-mandate-male', $mandateMale3);
        $manager->persist($mandateMale3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCommitteeData::class,
        ];
    }
}
