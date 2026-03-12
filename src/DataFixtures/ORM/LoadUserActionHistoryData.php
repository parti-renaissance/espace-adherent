<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Agora;
use App\Entity\Event\Event;
use App\Entity\UserActionHistory;
use App\History\UserActionHistoryTypeEnum;
use App\MyTeam\RoleEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadUserActionHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1', Adherent::class);
        /** @var Adherent $adherent2 */
        $adherent2 = $this->getReference('adherent-2', Adherent::class);
        /** @var Adherent $adherent3 */
        $adherent3 = $this->getReference('adherent-3', Adherent::class);
        /** @var Administrator $administrator1 */
        $administrator1 = $this->getReference('administrator-2', Administrator::class);
        /** @var Event $liveEvent1 */
        $liveEvent1 = $this->getReference('event-4', Event::class);
        /** @var Agora $agora1 */
        $agora1 = $this->getReference('agora-1', Agora::class);

        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::AGORA_MEMBERSHIP_ADD, new \DateTimeImmutable('-25 minutes'), [
            'agora' => $agora1->getName(),
            'agora_id' => $agora1->getId(),
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::AGORA_MEMBERSHIP_REMOVE, new \DateTimeImmutable('-22 minutes'), [
            'agora' => $agora1->getName(),
            'agora_id' => $agora1->getId(),
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::LIVE_VIEW, new \DateTimeImmutable('-20 minutes'), [
            'event' => $liveEvent1->getName(),
            'event_id' => $liveEvent1->getId(),
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::DELEGATED_ACCESS_ADD, new \DateTimeImmutable('-15 minutes'), [
            'delegator_uuid' => $adherent2->getUuid()->toString(),
            'scope' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            'features' => [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS, FeatureEnum::ELECTED_REPRESENTATIVE],
            'role' => RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER],
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::DELEGATED_ACCESS_EDIT, new \DateTimeImmutable('-14 minutes'), [
            'delegator_uuid' => $adherent2->getUuid()->toString(),
            'author_uuid' => $adherent3->getUuid()->toString(),
            'scope' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            'features' => [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS],
            'role' => RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER],
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::DELEGATED_ACCESS_REMOVE, new \DateTimeImmutable('-13 minutes'), [
            'delegator_uuid' => $adherent2->getUuid()->toString(),
            'scope' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            'features' => [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS],
            'role' => RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER],
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::LOGIN_FAILURE, new \DateTimeImmutable('-10 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PASSWORD_RESET_REQUEST, new \DateTimeImmutable('-9 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PASSWORD_RESET_VALIDATE, new \DateTimeImmutable('-8 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::LOGIN_SUCCESS, new \DateTimeImmutable('-7 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PROFILE_UPDATE, new \DateTimeImmutable('-6 minutes'), ['first_name']));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::IMPERSONATION_START, new \DateTimeImmutable('-5 minutes'), null, $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PROFILE_UPDATE, new \DateTimeImmutable('-4 minutes'), ['birthdate'], $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::IMPERSONATION_END, new \DateTimeImmutable('-3 minutes'), null, $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::EMAIL_CHANGE_REQUEST, new \DateTimeImmutable('-2 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::EMAIL_CHANGE_VALIDATE, new \DateTimeImmutable('-1 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::ROLE_ADD, new \DateTimeImmutable('-2 minutes'), [
            'role' => 'deputy',
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ], $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::ROLE_REMOVE, new \DateTimeImmutable('-1 minutes'), [
            'role' => 'deputy',
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ], $administrator1));

        $manager->flush();
    }

    private function create(
        Adherent $adherent,
        UserActionHistoryTypeEnum $type,
        ?\DateTimeInterface $date = null,
        ?array $data = null,
        ?Administrator $impersonator = null,
    ): UserActionHistory {
        return new UserActionHistory(
            $adherent,
            $type,
            $date ?? new \DateTimeImmutable('now'),
            $data,
            $impersonator
        );
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
            LoadGeoZoneData::class,
            LoadEventData::class,
        ];
    }
}
