<?php

namespace App\DataFixtures\ORM;

use App\Entity\ThematicCommunity\AdherentMembership;
use App\Entity\ThematicCommunity\ContactMembership;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadThematicCommunityMembershipData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $cm1 = new ContactMembership();
        $cm1->setContact($this->getReference('tc-contact-1'));
        $cm1->setCommunity($this->getReference('tc-sante'));
        $cm1->setMotivations([ThematicCommunityMembership::MOTIVATION_THINKING, ThematicCommunityMembership::MOTIVATION_ON_SPOT]);
        $manager->persist($cm1);

        $cm2 = new ContactMembership();
        $cm2->setContact($this->getReference('tc-contact-1'));
        $cm2->setCommunity($this->getReference('tc-education'));
        $cm2->setMotivations([ThematicCommunityMembership::MOTIVATION_ON_SPOT]);
        $manager->persist($cm2);

        $am = new AdherentMembership();
        $am->setAdherent($this->getReference('adherent-8'));
        $am->setCommunity($this->getReference('tc-education'));
        $am->setMotivations([ThematicCommunityMembership::MOTIVATION_ON_SPOT, ThematicCommunityMembership::MOTIVATION_INFORMATION]);
        $am->setAssociation(true);
        $am->setAssociationName('Association de ouf');
        $manager->persist($am);

        $amElected = new AdherentMembership();
        $amElected->setAdherent($this->getReference('adherent-5'));
        $amElected->setCommunity($this->getReference('tc-sante'));
        $amElected->setMotivations([ThematicCommunityMembership::MOTIVATION_THINKING]);
        $manager->persist($amElected);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadThematicCommunityData::class,
            LoadThematicCommunityContactData::class,
            LoadAdherentData::class,
        ];
    }
}
