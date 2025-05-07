<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Agora;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadAgoraData extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const UUID_1 = '82ad6422-cb82-4c04-b478-bfb421c740e0';
    public const UUID_2 = '75d47004-db80-4586-8fc5-e97cec58e5b4';
    public const UUID_3 = 'c3d0fb57-1ce9-441a-9978-8445fc01fa5c';

    public function load(ObjectManager $manager): void
    {
        /** @var Administrator $admin2 */
        $admin2 = $this->getReference('administrator-2', Administrator::class);
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1', Adherent::class);
        /** @var Adherent $adherent3 */
        $adherent3 = $this->getReference('adherent-3', Adherent::class);
        /** @var Adherent $adherent4 */
        $adherent4 = $this->getReference('adherent-4', Adherent::class);

        $manager->persist($agora1 = $this->createAgora(
            Uuid::fromString(self::UUID_1),
            'Première Agora',
            'Description première Agora',
            2,
            true,
            $adherent1,
            [$adherent3],
            $admin2
        ));
        $this->setReference('agora-1', $agora1);

        $manager->persist($this->createAgora(
            Uuid::fromString(self::UUID_2),
            'Deuxième Agora',
            'Description deuxième Agora',
            40,
            true,
            $adherent3,
            [$adherent1],
            $admin2
        ));

        $manager->persist($this->createAgora(
            Uuid::fromString(self::UUID_3),
            'Agora non publiée',
            'Description Agora non publiée',
            30,
            false,
            $adherent4,
            [],
            $admin2
        ));

        $manager->flush();
    }

    /**
     * @param Adherent[]|array $generalSecretaries
     */
    private function createAgora(
        UuidInterface $uuid,
        string $name,
        string $description,
        int $maxMembersCount,
        bool $published,
        Adherent $president,
        array $generalSecretaries,
        Administrator $administrator,
    ): Agora {
        $agora = new Agora($uuid);
        $agora->setName($name);
        $agora->description = $description;
        $agora->maxMembersCount = $maxMembersCount;
        $agora->published = $published;
        $agora->president = $president;
        $agora->generalSecretaries = new ArrayCollection($generalSecretaries);
        $agora->setCreatedByAdministrator($administrator);

        return $agora;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
        ];
    }
}
