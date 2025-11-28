<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentSegment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentSegmentData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $segment = new AdherentSegment();

        $segment->setLabel('ma super liste');
        $segment->setMemberIds([
            $this->getReference('adherent-7', Adherent::class)->getId(),
            $this->getReference('adherent-13', Adherent::class)->getId(),
        ]);
        $segment->setAuthor($this->getReference('adherent-8', Adherent::class));

        $manager->persist($segment);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
