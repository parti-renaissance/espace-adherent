<?php

namespace App\DataFixtures\ORM;

use App\Entity\Committee;
use App\Entity\CommitteeCandidaciesGroup;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadCommitteeV2CandidaciesGroupData extends Fixture implements DependentFixtureInterface
{
    public const CANDIDACIES_GROUP_1_UUID = '5d88db4a-9f3e-470e-8cc6-145dc6c7517a';
    public const CANDIDACIES_GROUP_2_UUID = '7f048f8e-0096-4cd2-b348-f19579223d6f';

    public function load(ObjectManager $manager)
    {
        /** @var Committee $commttee */
        $committee = $this->getReference('committee-v2-2');
        /** @var CommitteeElection $election */
        $election = $committee->getCurrentElection();
        $adherent5 = $this->getReference('adherent-5');

        $election->addCandidaciesGroups($list = new CommitteeCandidaciesGroup(Uuid::fromString(self::CANDIDACIES_GROUP_1_UUID)));
        $list->addCandidacy($candidate = new CommitteeCandidacy($election, Genders::FEMALE));

        $candidate->setCommitteeMembership($adherent5->getMembershipFor($committee));

        $election->addCandidaciesGroups($list = new CommitteeCandidaciesGroup(Uuid::fromString(self::CANDIDACIES_GROUP_2_UUID)));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeV2Data::class,
        ];
    }
}
