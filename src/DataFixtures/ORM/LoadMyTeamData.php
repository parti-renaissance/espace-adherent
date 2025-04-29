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
    public const TEAM_3_UUID = '9ea7ea50-146b-4022-87bc-b687b17e69ed';
    public const TEAM_4_UUID = 'f3d9cc9e-a1a1-49ad-8d6c-73d37cd2eb21';

    public const MEMBER_1_UUID = 'd11d6ddd-dfba-4972-97b2-4c0bdf289559';
    public const MEMBER_2_UUID = '7e82bb82-4b1e-4244-b484-7a51301df420';
    public const MEMBER_3_UUID = 'e0da56db-c4c6-4aa4-ad8d-7e9505dfdd93';

    public const MEMBER_4_UUID = 'b299bcf7-882b-4fce-8dc1-c1b24ceeeef5';
    public const MEMBER_5_UUID = 'b65b3b8e-ad92-46ae-a226-25e286828929';

    public const MEMBER_6_UUID = '5fb67010-aa4d-47e9-8183-d36e8fc6526d';
    public const MEMBER_7_UUID = '8671bb26-c88e-48ca-9858-fb53c495be01';

    public const MEMBER_8_UUID = '18753e30-9c37-4a0e-b498-fd1bafee4c46';

    public function load(ObjectManager $manager): void
    {
        $team1 = $this->createMyTeam(self::TEAM_1_UUID, $this->getReference('adherent-8', Adherent::class), ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $member1_1 = $this->createMember(
            $this->getReference('adherent-5', Adherent::class),
            RoleEnum::COMMUNICATION_MANAGER,
            [FeatureEnum::CONTACTS, FeatureEnum::MESSAGES, FeatureEnum::EVENTS],
            self::MEMBER_1_UUID
        );
        $this->setReference('my_team_member_1_1', $member1_1);
        $member1_2 = $this->createMember(
            $this->getReference('adherent-3', Adherent::class),
            RoleEnum::MOBILIZATION_MANAGER,
            [FeatureEnum::CONTACTS, FeatureEnum::EVENTS],
            self::MEMBER_2_UUID
        );
        $this->setReference('my_team_member_1_2', $member1_2);
        $member1_3 = $this->createMember(
            $this->getReference('senator-59', Adherent::class),
            RoleEnum::MOBILIZATION_MANAGER,
            FeatureEnum::ALL,
            self::MEMBER_3_UUID
        );
        $this->setReference('my_team_member_1_3', $member1_3);
        $team1->addMember($member1_1);
        $team1->addMember($member1_2);
        $team1->addMember($member1_3);
        $this->setReference('my-team-referent-1', $team1);

        $team2 = $this->createMyTeam(self::TEAM_2_UUID, $this->getReference('correspondent-1', Adherent::class), ScopeEnum::CORRESPONDENT);
        $member2_1 = $this->createMember(
            $this->getReference('adherent-5', Adherent::class),
            RoleEnum::LOGISTICS_MANAGER,
            [FeatureEnum::CONTACTS],
            self::MEMBER_4_UUID
        );
        $this->setReference('my_team_member_2_1', $member2_1);
        $member2_2 = $this->createMember(
            $this->getReference('adherent-9', Adherent::class),
            RoleEnum::COMPLIANCE_AND_FINANCE_MANAGER,
            [FeatureEnum::CONTACTS, FeatureEnum::MESSAGES, FeatureEnum::EVENTS, FeatureEnum::NEWS],
            self::MEMBER_5_UUID
        );
        $this->setReference('my_team_member_2_2', $member2_2);
        $team2->addMember($member2_1);
        $team2->addMember($member2_2);
        $this->setReference('my-team-correspondent-1', $team2);

        $team3 = $this->createMyTeam(self::TEAM_3_UUID, $this->getReference('adherent-19', Adherent::class), ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $member3_1 = $this->createMember(
            $this->getReference('adherent-7', Adherent::class),
            RoleEnum::COMMUNICATION_MANAGER,
            [FeatureEnum::EVENTS, FeatureEnum::PAP, FeatureEnum::SURVEY, FeatureEnum::TEAM, FeatureEnum::REFERRALS],
            self::MEMBER_6_UUID
        );
        $this->setReference('my_team_member_3_1', $member3_1);
        $member3_2 = $this->createMember(
            $this->getReference('referent-child', Adherent::class),
            RoleEnum::LOGISTICS_MANAGER,
            [],
            self::MEMBER_7_UUID
        );
        $this->setReference('my_team_member_3_2', $member3_2);
        $team3->addMember($member3_1);
        $team3->addMember($member3_2);
        $this->setReference('my-team-referent-2', $team3);

        $team_lc_1 = $this->createMyTeam(self::TEAM_4_UUID, $this->getReference('senatorial-candidate', Adherent::class), ScopeEnum::LEGISLATIVE_CANDIDATE);
        $member_lc_1 = $this->createMember(
            $this->getReference('adherent-5', Adherent::class),
            RoleEnum::COMMUNICATION_MANAGER,
            [FeatureEnum::NEWS, FeatureEnum::EVENTS, FeatureEnum::CONTACTS],
            self::MEMBER_8_UUID
        );
        $this->setReference('my_team_lc_member_1', $member_lc_1);
        $team_lc_1->addMember($member_lc_1);
        $this->setReference('my-team-legislative-candidate-1', $team_lc_1);

        $manager->persist($team1);
        $manager->persist($team2);
        $manager->persist($team3);
        $manager->persist($team_lc_1);

        $manager->flush();
    }

    public function getDependencies(): array
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
        ?string $uuid = null,
    ): Member {
        return new Member($adherent, $role, $scopeFeatures, Uuid::fromString($uuid));
    }
}
