<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\MyTeam\Member;
use App\Entity\MyTeam\MyTeam;
use App\MyTeam\RoleEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadMyTeamData extends Fixture implements DependentFixtureInterface
{
    public const TEAM_1_UUID = '7fab9d6c-71a1-4257-b42b-c6b9b2350a26';
    public const TEAM_2_UUID = '17921a6c-cf1c-4b49-9aac-06bd3913c3f7';

    public const MEMBER_1_UUID = 'd11d6ddd-dfba-4972-97b2-4c0bdf289559';
    public const MEMBER_2_UUID = '7e82bb82-4b1e-4244-b484-7a51301df420';
    public const MEMBER_3_UUID = 'b299bcf7-882b-4fce-8dc1-c1b24ceeeef5';
    public const MEMBER_4_UUID = 'b65b3b8e-ad92-46ae-a226-25e286828929';

    public function load(ObjectManager $manager)
    {
        $team1 = $this->createMyTeam(self::TEAM_1_UUID, $this->getReference('adherent-8'), ScopeEnum::REFERENT);
        $team1->addMember($this->createMember(
            $this->getReference('adherent-2'),
            RoleEnum::COMMUNICATION_MANAGER,
            [FeatureEnum::CONTACTS, FeatureEnum::MESSAGES, FeatureEnum::EVENTS],
            self::MEMBER_1_UUID
        ));
        $team1->addMember($this->createMember(
            $this->getReference('adherent-3'),
            RoleEnum::MOBILIZATION_MANAGER,
            [FeatureEnum::CONTACTS, FeatureEnum::EVENTS],
            self::MEMBER_2_UUID
        ));
        $this->setReference('my-team-referent_1', $team1);

        $team2 = $this->createMyTeam(self::TEAM_2_UUID, $this->getReference('correspondent-1'), ScopeEnum::CORRESPONDENT);
        $team2->addMember($this->createMember(
            $this->getReference('adherent-2'),
            RoleEnum::LOGISTICS_MANAGER,
            [FeatureEnum::CONTACTS],
            self::MEMBER_3_UUID
        ));
        $team2->addMember($this->createMember(
            $this->getReference('adherent-9'),
            RoleEnum::COMPLIANCE_AND_FINANCE_MANAGERS,
            [FeatureEnum::CONTACTS, FeatureEnum::MESSAGES, FeatureEnum::EVENTS],
            self::MEMBER_4_UUID
        ));
        $this->setReference('my-team-correspondent-1', $team2);

        $manager->persist($team1);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
            LoadGeoZoneData::class,
        ];
    }

    private function createMyTeam(string $uuid, Adherent $owner, string $scope): MyTeam
    {
        return new MyTeam($owner, $scope, Uuid::fromString($uuid));
    }

    private function createMember(
        Adherent $adherent,
        string $role,
        array $scopeFeatures = [],
        string $uuid = null
    ): Member {
        return new Member($adherent, $role, $scopeFeatures, Uuid::fromString($uuid));
    }
}
