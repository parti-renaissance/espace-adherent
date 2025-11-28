<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Team\Member;
use App\Entity\Team\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadTeamData extends Fixture implements DependentFixtureInterface
{
    public const TEAM_1_UUID = '3deeb1f5-819e-4629-85a1-eb75c916ce2f';
    public const TEAM_2_UUID = '6434f2ac-edd0-412a-9c4b-99ab4b039146';
    public const TEAM_3_UUID = 'c608c447-8c45-4ee7-b39c-7d0217d1c6db';
    public const TEAM_4_UUID = 'ba9ab5dd-c8da-4721-8acb-5a96e285aec3';
    public const TEAM_5_UUID = 'a4ad9bde-9fd5-4eda-92e5-9e5576cac9e2';
    public const TEAM_6_UUID = '389a40c3-d8c1-4611-bf52-f172088066db';

    public const MEMBER_1_UUID = '934ccb1d-9742-41e2-87e2-4ee439565f6a';
    public const MEMBER_2_UUID = 'a8981a72-4660-4cb0-bc08-725a0c8c9afe';
    public const MEMBER_3_UUID = 'dc7a0f15-591a-4e11-a09f-5a5559b64cf4';
    public const MEMBER_4_UUID = '3b05dde9-acd0-43b7-83a5-a67cda9a7946';
    public const MEMBER_5_UUID = '5a0d85bf-2c66-4bc3-aa29-c07b03951bc4';
    public const MEMBER_6_UUID = 'a33fa2f6-e7ee-4755-a399-bfc93015529e';
    public const MEMBER_7_UUID = '76dd7e44-1a7e-4d2f-bdd8-018690ac5211';
    public const MEMBER_8_UUID = '71b6d39b-d7f9-4b3d-9ad2-9cc9881eb8a7';
    public const MEMBER_9_UUID = '538ebe4a-afe6-4af7-a264-c3d82ff98222';
    public const MEMBER_10_UUID = 'ddd7755a-a5fc-4030-b409-8333d8719c3c';

    public function load(ObjectManager $manager): void
    {
        $team1 = $this->createTeam(self::TEAM_1_UUID, 'Première équipe de phoning');
        $team1->setCreatedAt(new \DateTime('-12 hours'));
        $team1->addMember($this->createMember(self::MEMBER_1_UUID, $this->getReference('adherent-1', Adherent::class)));
        $team1->addMember($this->createMember(self::MEMBER_2_UUID, $this->getReference('adherent-3', Adherent::class)));
        $team1->addMember($this->createMember(self::MEMBER_3_UUID, $this->getReference('adherent-12', Adherent::class)));
        $this->setReference('team-1', $team1);

        $team2 = $this->createTeam(self::TEAM_2_UUID, 'Deuxième équipe de phoning');
        $team2->setCreatedAt(new \DateTime('-9 hours'));
        $member1 = $this->createMember(self::MEMBER_4_UUID, $this->getReference('adherent-4', Adherent::class));
        $member1->setCreatedAt(new \DateTime('-1 hours'));
        $team2->addMember($member1);
        $member2 = $this->createMember(self::MEMBER_5_UUID, $this->getReference('adherent-3', Adherent::class));
        $member2->setCreatedAt(new \DateTime('-2 hours'));
        $team2->addMember($member2);
        $member3 = $this->createMember(self::MEMBER_6_UUID, $this->getReference('adherent-12', Adherent::class));
        $member3->setCreatedAt(new \DateTime('-3 hours'));
        $team2->addMember($member3);
        $member4 = $this->createMember(self::MEMBER_7_UUID, $this->getReference('deputy-75-1', Adherent::class));
        $member4->setCreatedAt(new \DateTime('-4 hours'));
        $team2->addMember($member4);
        $this->setReference('team-2', $team2);

        $team3 = $this->createTeam(
            self::TEAM_3_UUID,
            'Équipe locale du département 92',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92')
        );
        $team3->setCreatedAt(new \DateTime('-8 hours'));
        $team3->addMember($this->createMember(self::MEMBER_8_UUID, $this->getReference('adherent-1', Adherent::class)));

        $team4 = $this->createTeam(
            self::TEAM_4_UUID,
            'Équipe locale de la ville de Lille (59350)',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_59350')
        );
        $team4->setCreatedAt(new \DateTime('-7 hours'));
        $team4->addMember($this->createMember(self::MEMBER_9_UUID, $this->getReference('adherent-4', Adherent::class)));

        $team5 = $this->createTeam(
            self::TEAM_5_UUID,
            'Équipe locale de la ville de Nice (06088)',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_06088')
        );
        $team5->setCreatedAt(new \DateTime('-7 hours'));
        $team5->addMember($this->createMember(self::MEMBER_10_UUID, $this->getReference('adherent-4', Adherent::class)));

        $manager->persist($team = $this->createTeam(
            self::TEAM_6_UUID,
            'Équipe à supprimer',
        ));
        $team->setCreatedAt(new \DateTime('-8 hours'));
        $team->addMember($this->createMember(Uuid::uuid4()->toString(), $this->getReference('adherent-1', Adherent::class)));

        $manager->persist($team1);
        $manager->persist($team2);
        $manager->persist($team3);
        $manager->persist($team4);
        $manager->persist($team5);

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

    private function createTeam(string $uuid, string $name, ?Zone $zone = null): Team
    {
        return new Team(Uuid::fromString($uuid), $name, [], $zone);
    }

    private function createMember(string $uuid, Adherent $adherent): Member
    {
        return new Member(Uuid::fromString($uuid), $adherent);
    }
}
