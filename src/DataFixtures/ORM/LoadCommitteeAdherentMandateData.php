<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCommitteeAdherentMandateData extends AbstractFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $committee1 = $this->getReference('committee-1');
        $committee3 = $this->getReference('committee-3');
        $committee4 = $this->getReference('committee-4');
        $committee5 = $this->getReference('committee-5');
        $committee6 = $this->getReference('committee-6');
        $committee7 = $this->getReference('committee-7');
        $committee8 = $this->getReference('committee-8');
        $committee9 = $this->getReference('committee-9');
        $committee10 = $this->getReference('committee-10');
        $committee11 = $this->getReference('committee-11');
        $committee12 = $this->getReference('committee-12');
        $committee13 = $this->getReference('committee-13');
        $committee15 = $this->getReference('committee-15');

        $carl = $this->getReference('adherent-2');
        $jacques = $this->getReference('adherent-3');
        $lucie = $this->getReference('adherent-4');
        $gisele = $this->getReference('adherent-5');
        $francis = $this->getReference('adherent-7');
        $referent = $this->getReference('adherent-8');
        $laura = $this->getReference('adherent-9');
        $martine = $this->getReference('adherent-10');
        $elodie = $this->getReference('adherent-11');
        $kiroule = $this->getReference('adherent-12');
        $adherent31 = $this->getReference('adherent-31');
        $adherent32 = $this->getReference('adherent-32');
        $senatorialCandidate = $this->getReference('senatorial-candidate');

        // Designed adherent mandates
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee1, $gisele, new \DateTime('2020-06-06')));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee1, $carl, new \DateTime('2020-08-08')));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee3, $lucie, new \DateTime('2020-06-06')));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee3, $jacques, new \DateTime('2020-08-08')));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee1, $jacques, new \DateTime('2018-01-01'), new \DateTime('2019-12-31')));

        // Committee supervisor mandates
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee1, $jacques, new \DateTime('2017-01-12 13:25:54'), null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee1, $gisele, new \DateTime('2017-05-05 11:11:11'), new \DateTime('2018-05-05 12:12:12'), CommitteeMandateQualityEnum::SUPERVISOR, true));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee3, $gisele, new \DateTime('2017-01-27 17:09:25'), null, CommitteeMandateQualityEnum::SUPERVISOR, true));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee5, $gisele, new \DateTime('-2 months'), null, CommitteeMandateQualityEnum::SUPERVISOR, true));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee3, $francis, new \DateTime('2017-01-26 16:08:24'), null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee4, $francis, null, null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee5, $francis, null, null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee10, $referent, null, null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee6, $laura, null, null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee7, $martine, new \DateTime('2020-10-11 11:11:11'), null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee8, $elodie, null, null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee11, $elodie, new \DateTime('2021-01-01 01:01:01'), null, CommitteeMandateQualityEnum::SUPERVISOR, true));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee9, $kiroule, null, null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee13, $adherent31, new \DateTime('-2 months'), null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee15, $adherent32, new \DateTime('-2 months'), null, CommitteeMandateQualityEnum::SUPERVISOR));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee3, $senatorialCandidate, new \DateTime('2017-01-27 17:09:25'), null, CommitteeMandateQualityEnum::SUPERVISOR, true));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee7, $senatorialCandidate, new \DateTime('2020-10-10 10:10:10'), null, CommitteeMandateQualityEnum::SUPERVISOR, true));
        $manager->persist(CommitteeAdherentMandate::createForCommittee($committee12, $senatorialCandidate, new \DateTime('-2 months'), null, CommitteeMandateQualityEnum::SUPERVISOR));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeData::class,
        ];
    }
}
