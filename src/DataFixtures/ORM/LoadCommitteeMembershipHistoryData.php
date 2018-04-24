<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use AppBundle\Entity\Reporting\CommitteeMembershipHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCommitteeMembershipHistoryData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $memberships = $manager->getRepository(CommitteeMembership::class)->findAll();

        foreach ($memberships as $membership) {
            $event = new CommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN(), $membership->getSubscriptionDate());

            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [LoadAdherentData::class];
    }
}
