<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Team\Member;
use App\Entity\Team\Team;
use App\Team\TypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadTeamData extends Fixture implements DependentFixtureInterface
{
    public const TEAM_1_UUID = '3deeb1f5-819e-4629-85a1-eb75c916ce2f';
    public const TEAM_2_UUID = '6434f2ac-edd0-412a-9c4b-99ab4b039146';

    public const MEMBER_1_UUID = '934ccb1d-9742-41e2-87e2-4ee439565f6a';
    public const MEMBER_2_UUID = 'a8981a72-4660-4cb0-bc08-725a0c8c9afe';
    public const MEMBER_3_UUID = 'dc7a0f15-591a-4e11-a09f-5a5559b64cf4';
    public const MEMBER_4_UUID = '3b05dde9-acd0-43b7-83a5-a67cda9a7946';
    public const MEMBER_5_UUID = '5a0d85bf-2c66-4bc3-aa29-c07b03951bc4';
    public const MEMBER_6_UUID = 'a33fa2f6-e7ee-4755-a399-bfc93015529e';
    public const MEMBER_7_UUID = '76dd7e44-1a7e-4d2f-bdd8-018690ac5211';

    public function load(ObjectManager $manager)
    {
        $team1 = $this->createTeam(self::TEAM_1_UUID, TypeEnum::PHONING, 'Première équipe de phoning');
        $team1->addMember($this->createMember(self::MEMBER_1_UUID, $this->getReference('adherent-1')));
        $team1->addMember($this->createMember(self::MEMBER_2_UUID, $this->getReference('adherent-3')));
        $team1->addMember($this->createMember(self::MEMBER_3_UUID, $this->getReference('adherent-12')));
        $this->setReference('team-1', $team1);

        $team2 = $this->createTeam(self::TEAM_2_UUID, TypeEnum::PHONING, 'Deuxième équipe de phoning');
        $team2->addMember($this->createMember(self::MEMBER_4_UUID, $this->getReference('adherent-4')));
        $team2->addMember($this->createMember(self::MEMBER_5_UUID, $this->getReference('adherent-3')));
        $team2->addMember($this->createMember(self::MEMBER_6_UUID, $this->getReference('adherent-12')));
        $team2->addMember($this->createMember(self::MEMBER_7_UUID, $this->getReference('deputy-75-1')));
        $this->setReference('team-2', $team2);

        $manager->persist($team1);
        $manager->persist($team2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
        ];
    }

    private function createTeam(string $uuid, string $type, string $name): Team
    {
        return new Team(Uuid::fromString($uuid), $type, $name);
    }

    private function createMember(string $uuid, Adherent $adherent): Member
    {
        return new Member(Uuid::fromString($uuid), $adherent);
    }
}
