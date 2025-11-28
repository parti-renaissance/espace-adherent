<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\AdministratorActionHistory;
use App\History\AdministratorActionHistoryTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdministratorActionHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Administrator $administrator1 */
        $administrator1 = $this->getReference('administrator-2', Administrator::class);
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1', Adherent::class);

        $manager->persist($this->create($administrator1, AdministratorActionHistoryTypeEnum::LOGIN_FAILURE, new \DateTime('-10 minutes')));
        $manager->persist($this->create($administrator1, AdministratorActionHistoryTypeEnum::LOGIN_SUCCESS, new \DateTime('-9 minutes')));
        $manager->persist($this->create($administrator1, AdministratorActionHistoryTypeEnum::IMPERSONATION_START, new \DateTime('-8 minutes'), [
            'adherent_uuid' => $adherent1->getUuid()->toString(),
        ]));
        $manager->persist($this->create($administrator1, AdministratorActionHistoryTypeEnum::IMPERSONATION_END, new \DateTime('-7 minutes'), [
            'adherent_uuid' => $adherent1->getUuid()->toString(),
        ]));
        $manager->persist($this->create($administrator1, AdministratorActionHistoryTypeEnum::EXPORT, new \DateTime('-6 minutes'), [
            'route' => 'admin_app_adherent_export',
            'parameters' => [
                'filter1' => 'value1',
                'filter2' => 'value2',
            ],
        ]));

        $manager->flush();
    }

    private function create(
        Administrator $administrator,
        AdministratorActionHistoryTypeEnum $type,
        ?\DateTimeInterface $date = null,
        ?array $data = null,
    ): AdministratorActionHistory {
        return new AdministratorActionHistory(
            $administrator,
            $type,
            $date ?? new \DateTime('now'),
            $data
        );
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
        ];
    }
}
