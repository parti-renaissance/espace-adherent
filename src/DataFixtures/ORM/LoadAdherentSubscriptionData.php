<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ActivitySubscription;
use AppBundle\Entity\Adherent;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadAdherentSubscriptionData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $adherent1 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $adherent2 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $adherent3 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_3_UUID);

        $activity1 = new ActivitySubscription($adherent1, $adherent2);
        $activity2 = new ActivitySubscription($adherent1, $adherent3);
        $activity3 = new ActivitySubscription($adherent3, $adherent1);
        $activity4 = new ActivitySubscription($adherent2, $adherent3);

        $manager->persist($activity1);
        $manager->persist($activity2);
        $manager->persist($activity3);
        $manager->persist($activity4);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
