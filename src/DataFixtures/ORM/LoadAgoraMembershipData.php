<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\AgoraMembership;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadAgoraMembershipData extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const UUID_1 = 'de9b01c1-6e12-47fe-93fb-54d42f428edb';
    public const UUID_2 = 'b644dac5-84ed-4b03-be23-5343c5baa94d';
    public const UUID_3 = 'c9d5d1d6-04b6-4ff0-9376-6071481a7525';

    public function load(ObjectManager $manager): void
    {
        $agora1 = $this->getReference('agora-1', Agora::class);
        $agora2 = $this->getReference('agora-2', Agora::class);
        $adherent2 = $this->getReference('adherent-2', Adherent::class);
        $adherent4 = $this->getReference('adherent-4', Adherent::class);
        $adherent5 = $this->getReference('adherent-5', Adherent::class);
        $adherent6 = $this->getReference('adherent-6', Adherent::class);

        $manager->persist($this->createAgoraMembership(
            Uuid::fromString(self::UUID_1),
            $agora1,
            $adherent2
        ));

        $manager->persist($this->createAgoraMembership(
            Uuid::fromString(self::UUID_2),
            $agora1,
            $adherent4
        ));

        $manager->persist($this->createAgoraMembership(
            Uuid::fromString(self::UUID_3),
            $agora2,
            $adherent5
        ));

        $manager->persist($this->createAgoraMembership(
            Uuid::uuid4(),
            $agora2,
            $adherent6
        ));

        $manager->flush();
    }

    private function createAgoraMembership(
        UuidInterface $uuid,
        Agora $agora,
        Adherent $adherent,
    ): AgoraMembership {
        $agoraMembership = new AgoraMembership($uuid);
        $agoraMembership->agora = $agora;
        $agoraMembership->adherent = $adherent;

        return $agoraMembership;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadAgoraData::class,
        ];
    }
}
