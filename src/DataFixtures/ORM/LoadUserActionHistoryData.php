<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
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

        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::LIVE_VIEW, new \DateTime('-20 minutes'), [
            'event' => $liveEvent1->getName(),
            'event_id' => $liveEvent1->getId(),
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::TEAM_MEMBER_ADD, new \DateTime('-15 minutes'), [
            'delegator_uuid' => $adherent2->getUuid()->toString(),
            'scope' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            'features' => [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS, FeatureEnum::ELECTED_REPRESENTATIVE],
            'role' => RoleEnum::MOBILIZATION_MANAGER,
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::TEAM_MEMBER_EDIT, new \DateTime('-14 minutes'), [
            'delegator_uuid' => $adherent2->getUuid()->toString(),
            'author_uuid' => $adherent3->getUuid()->toString(),
            'scope' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            'features' => [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS],
            'role' => RoleEnum::MOBILIZATION_MANAGER,
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::TEAM_MEMBER_REMOVE, new \DateTime('-13 minutes'), [
            'delegator_uuid' => $adherent2->getUuid()->toString(),
            'scope' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            'features' => [FeatureEnum::MESSAGES, FeatureEnum::CONTACTS],
            'role' => RoleEnum::MOBILIZATION_MANAGER,
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ]));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::LOGIN_FAILURE, new \DateTime('-10 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PASSWORD_RESET_REQUEST, new \DateTime('-9 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PASSWORD_RESET_VALIDATE, new \DateTime('-8 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::LOGIN_SUCCESS, new \DateTime('-7 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PROFILE_UPDATE, new \DateTime('-6 minutes'), ['first_name']));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::IMPERSONATION_START, new \DateTime('-5 minutes'), null, $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PROFILE_UPDATE, new \DateTime('-4 minutes'), ['birthdate'], $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::IMPERSONATION_END, new \DateTime('-3 minutes'), null, $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::EMAIL_CHANGE_REQUEST, new \DateTime('-2 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::EMAIL_CHANGE_VALIDATE, new \DateTime('-1 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::ROLE_ADD, new \DateTime('-2 minutes'), [
            'role' => 'deputy',
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_city_92024')->getNameCode()],
        ], $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::ROLE_REMOVE, new \DateTime('-1 minutes'), [
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
            $date ?? new \DateTime('now'),
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
