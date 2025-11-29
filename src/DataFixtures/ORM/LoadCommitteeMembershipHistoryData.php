<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\CommitteeMembership;
use App\Entity\Reporting\CommitteeMembershipAction;
use App\Entity\Reporting\CommitteeMembershipHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCommitteeMembershipHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $memberships = $manager->getRepository(CommitteeMembership::class)->findAll();

        foreach ($memberships as $membership) {
            $manager->persist(new CommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN(), $membership->getSubscriptionDate()));
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadCommitteeData::class,
            LoadCommitteeV1Data::class,
        ];
    }
}
