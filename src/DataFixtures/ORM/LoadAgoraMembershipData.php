<?php

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

    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $adherent2 */
        $adherent2 = $this->getReference('adherent-2', Adherent::class);
        /** @var Adherent $adherent4 */
        $adherent4 = $this->getReference('adherent-4', Adherent::class);
        /** @var Agora $agora1 */
        $agora1 = $this->getReference('agora-1', Agora::class);

        $manager->persist($this->createAgoraMembership(
            Uuid::fromString(self::UUID_1),
            $adherent2,
            $agora1
        ));

        $manager->persist($this->createAgoraMembership(
            Uuid::fromString(self::UUID_2),
            $adherent4,
            $agora1
        ));

        $manager->flush();
    }

    private function createAgoraMembership(
        UuidInterface $uuid,
        Adherent $adherent,
        Agora $agora,
    ): AgoraMembership {
        $agoraMembership = new AgoraMembership($uuid);
        $agoraMembership->adherent = $adherent;
        $agoraMembership->agora = $agora;

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
