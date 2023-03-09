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
    public const CANDIDACIES_GROUP_3_UUID = 'f8a426f3-8014-4803-95b5-8077300755c6';

    public const CANDIDACY_1_UUID = '50dd9672-69ca-46e1-9353-c2e0d6c03333';
    public const CANDIDACY_2_UUID = 'd229453d-a9dc-4392-a320-d9536c93b5fe';

    public function load(ObjectManager $manager)
    {
        /** @var Committee $commttee */
        $committee = $this->getReference('committee-v2-2');
        /** @var CommitteeElection $election1 */
        $election1 = $committee->getCurrentElection();
        $adherent5 = $this->getReference('adherent-5');

        $election1->addCandidaciesGroups($list = new CommitteeCandidaciesGroup(Uuid::fromString(self::CANDIDACIES_GROUP_1_UUID)));
        $list->addCandidacy($candidate = new CommitteeCandidacy($election1, Genders::FEMALE, Uuid::fromString(self::CANDIDACY_1_UUID)));

        $candidate->setCommitteeMembership($adherent5->getMembershipFor($committee));

        $election1->addCandidaciesGroups($list = new CommitteeCandidaciesGroup(Uuid::fromString(self::CANDIDACIES_GROUP_2_UUID)));

        /** @var CommitteeElection $election2 */
        $election2 = $this->getReference('committee-election-2');
        $election2->addCandidaciesGroups($list = new CommitteeCandidaciesGroup(Uuid::fromString(self::CANDIDACIES_GROUP_3_UUID)));
        $list->addCandidacy($candidate = new CommitteeCandidacy($election2, Genders::FEMALE, Uuid::fromString(self::CANDIDACY_2_UUID)));
        $candidate->setCommitteeMembership($adherent5->getMembershipFor($committee));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeV2Data::class,
        ];
    }
}
