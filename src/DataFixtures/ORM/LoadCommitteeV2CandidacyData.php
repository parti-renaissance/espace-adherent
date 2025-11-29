<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidaciesGroup;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadCommitteeV2CandidacyData extends Fixture implements DependentFixtureInterface
{
    public const CANDIDACIES_GROUP_1_UUID = '5d88db4a-9f3e-470e-8cc6-145dc6c7517a';
    public const CANDIDACIES_GROUP_2_UUID = '7f048f8e-0096-4cd2-b348-f19579223d6f';
    public const CANDIDACIES_GROUP_3_UUID = 'f8a426f3-8014-4803-95b5-8077300755c6';

    public const CANDIDACY_1_UUID = '50dd9672-69ca-46e1-9353-c2e0d6c03333';
    public const CANDIDACY_2_UUID = 'd229453d-a9dc-4392-a320-d9536c93b5fe';

    public function load(ObjectManager $manager): void
    {
        /** @var Committee $committee */
        $committee = $this->getReference('committee-v2-1', Committee::class);
        /** @var CommitteeElection $election */
        $election = $committee->getCurrentElection();
        $candidacyGroup = null;

        foreach (range(51, 60) as $index) {
            if (null === $candidacyGroup || 0 === $index % 3) {
                $election->addCandidaciesGroups($candidacyGroup = new CommitteeCandidaciesGroup());
            }
            /** @var Adherent $adherent */
            $adherent = $this->getReference('adherent-'.$index, Adherent::class);
            $candidacyGroup->addCandidacy($candidate = new CommitteeCandidacy($election, $adherent->getGender()));
            $candidate->setCommitteeMembership($adherent->getMembershipFor($committee));
        }

        /** @var Committee $committee */
        $committee = $this->getReference('committee-v2-2', Committee::class);
        /** @var CommitteeElection $election1 */
        $election1 = $committee->getCurrentElection();
        $adherent5 = $this->getReference('adherent-5', Adherent::class);

        $election1->addCandidaciesGroups($list = new CommitteeCandidaciesGroup(Uuid::fromString(self::CANDIDACIES_GROUP_1_UUID)));
        $list->addCandidacy($candidate = new CommitteeCandidacy($election1, Genders::FEMALE, Uuid::fromString(self::CANDIDACY_1_UUID)));
        $list->setCreatedAt(new \DateTime('-4 hours'));
        $candidate->setCommitteeMembership($adherent5->getMembershipFor($committee));
        $election1->addCandidaciesGroups($list = new CommitteeCandidaciesGroup(Uuid::fromString(self::CANDIDACIES_GROUP_2_UUID)));
        $list->setCreatedAt(new \DateTime('-3 hours'));

        $election2 = $this->getReference('committee-election-2', CommitteeElection::class);
        $election2->addCandidaciesGroups($list = new CommitteeCandidaciesGroup(Uuid::fromString(self::CANDIDACIES_GROUP_3_UUID)));
        $list->addCandidacy($candidate = new CommitteeCandidacy($election2, Genders::FEMALE, Uuid::fromString(self::CANDIDACY_2_UUID)));
        $candidate->setCommitteeMembership($adherent5->getMembershipFor($committee));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadCommitteeData::class,
        ];
    }
}
