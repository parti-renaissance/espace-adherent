<?php

namespace App\DataFixtures\ORM;

use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\MyTeam\RoleEnum;
use App\Scope\FeatureEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDelegatedAccessData extends Fixture implements DependentFixtureInterface
{
    public const ACCESS_UUID_1 = '2e80d106-4bcb-4b28-97c9-3856fc235b27';
    public const ACCESS_UUID_2 = 'f4ce89da-1272-4a01-a47e-4ce5248ce018';
    public const ACCESS_UUID_3 = '96076afb-2243-4251-97fe-8201d50c3256';
    public const ACCESS_UUID_4 = '411faa64-202d-4ff2-91ce-c98b29af28ef';
    public const ACCESS_UUID_5 = 'd2315289-a3fd-419c-a3dd-3e1ff71b754d';
    public const ACCESS_UUID_6 = '7fdf8fb0-1628-4500-b0b2-34c40cc27a2c';
    public const ACCESS_UUID_7 = '08f40730-d807-4975-8773-69d8fae1da74';
    public const ACCESS_UUID_8 = '01ddb89b-25be-4ccb-a90f-8338c42e7e58';
    public const ACCESS_UUID_9 = '2c0107d4-75d6-4874-ad38-2060112c0049';
    public const ACCESS_UUID_10 = '13208e84-450d-4d44-86e7-fb5f0b7e642c';
    public const ACCESS_UUID_11 = 'ef339f8e-e9d0-4f22-b98f-1a7526246cad';
    public const ACCESS_UUID_12 = '433e368f-fd4e-4a24-9f01-b667f8e3b9f2';
    public const ACCESS_UUID_13 = '6d2506a7-bec7-45a1-a5ee-8f8b48daa5ec';
    public const ACCESS_UUID_14 = '2c6134f7-4312-45c4-9ab7-89f2b0731f86';
    public const ACCESS_UUID_15 = '689757d2-dea5-49d1-95fe-281fc860ff77';

    public function load(ObjectManager $manager)
    {
        // full access, no committees or cities restriction
        $delegatedAccess1 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_1));
        $delegatedAccess1->setDelegated($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess1->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess1->setRole('Collaborateur parlementaire');
        $delegatedAccess1->setType('deputy');
        $delegatedAccess1->setAccesses(DelegatedAccess::ACCESSES);

        $manager->persist($delegatedAccess1);

        // access to 2 tabs, no committees or cities restriction
        $delegatedAccess2 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_2));
        $delegatedAccess2->setDelegated($this->getReference('adherent-4')); // luciole1989@spambox.fr
        $delegatedAccess2->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess2->setRole('Collaborateur parlementaire');
        $delegatedAccess2->setType('deputy');
        $delegatedAccess2->setAccesses([DelegatedAccess::ACCESS_EVENTS, DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess2);

        // access to 2 tabs, with cities restriction
        $delegatedAccess3 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_3));
        $delegatedAccess3->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess3->setDelegator($this->getReference('deputy-ch-li')); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess3->setRole('Collaborateur parlementaire');
        $delegatedAccess3->setType('deputy');
        $delegatedAccess3->setAccesses([DelegatedAccess::ACCESS_MESSAGES, DelegatedAccess::ACCESS_EVENTS]);
        $delegatedAccess3->setScopeFeatures([FeatureEnum::MESSAGES, FeatureEnum::EVENTS]);
        $delegatedAccess3->setRestrictedCities(['59360', '59350', '59044', '59002']);

        $manager->persist($delegatedAccess3);

        // second access to same user, but of type senator, with different accesses
        $delegatedAccess4 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_4));
        $delegatedAccess4->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess4->setDelegator($this->getReference('senator-59')); // senateur@en-marche-dev.fr
        $delegatedAccess4->setRole('Collaborateur parlementaire');
        $delegatedAccess4->setType('senator');
        $delegatedAccess4->setAccesses([DelegatedAccess::ACCESS_MESSAGES, DelegatedAccess::ACCESS_ADHERENTS]);
        $delegatedAccess4->setScopeFeatures([FeatureEnum::MESSAGES, FeatureEnum::CONTACTS]);

        $manager->persist($delegatedAccess4);

        // second deputy access to same user, with different accesses
        $delegatedAccess5 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_5));
        $delegatedAccess5->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess5->setDelegator($this->getReference('deputy-75-2')); // deputy-75-2@en-marche-dev.fr
        $delegatedAccess5->setRole('Collaborateur parlementaire');
        $delegatedAccess5->setType('deputy');
        $delegatedAccess5->setAccesses([DelegatedAccess::ACCESS_ADHERENTS]);
        $delegatedAccess5->setScopeFeatures([FeatureEnum::CONTACTS]);

        $manager->persist($delegatedAccess5);

        // no accesses
        $delegatedAccess6 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_6));
        $delegatedAccess6->setDelegated($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess6->setDelegator($this->getReference('senator-59')); // senateur@en-marche-dev.fr
        $delegatedAccess6->setRole('Collaborateur parlementaire');
        $delegatedAccess6->setType('senator');

        $manager->persist($delegatedAccess6);

        // access from referent
        $delegatedAccess7 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_7));
        $delegatedAccess7->setDelegated($this->getReference('senator-59')); // senateur@en-marche-dev.fr
        $delegatedAccess7->setDelegator($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess7->setRole('Collaborateur parlementaire');
        $delegatedAccess7->setType('referent');
        $delegatedAccess7->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
            DelegatedAccess::ACCESS_MESSAGES,
        ]);
        $delegatedAccess7->setScopeFeatures(FeatureEnum::AVAILABLE_FOR_DELEGATED_ACCESSES);

        $manager->persist($delegatedAccess7);

        $delegatedAccess10 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_10));
        $delegatedAccess10->setDelegated($this->getReference('municipal-manager-lille')); // jean-claude.dusse@example.fr
        $delegatedAccess10->setDelegator($this->getReference('adherent-8')); // referent@en-marche-dev.fr
        $delegatedAccess10->setRole('Responsable Digital');
        $delegatedAccess10->setType('referent');
        $delegatedAccess10->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
        ]);

        $manager->persist($delegatedAccess10);

        // access from candidate
        $delegatedAccess8 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_8));
        $delegatedAccess8->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess8->setDelegator($this->getReference('adherent-3')); // jacques.picard@en-marche.fr
        $delegatedAccess8->setRole('Candidat délégué');
        $delegatedAccess8->setType('candidate');
        $delegatedAccess8->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
            DelegatedAccess::ACCESS_MESSAGES,
            DelegatedAccess::ACCESS_EVENTS,
            DelegatedAccess::ACCESS_FILES,
            DelegatedAccess::ACCESS_POLLS,
            DelegatedAccess::ACCESS_JECOUTE,
            DelegatedAccess::ACCESS_JECOUTE_REGION,
            DelegatedAccess::ACCESS_JECOUTE_NEWS,
        ]);
        $delegatedAccess8->setScopeFeatures([
            FeatureEnum::MESSAGES,
            FeatureEnum::CONTACTS,
            FeatureEnum::EVENTS,
            FeatureEnum::NEWS,
            FeatureEnum::RIPOSTES,
            FeatureEnum::SURVEY,
        ]);
        $delegatedAccess8->setRestrictedCities(['77288', '59002']);
        $delegatedAccess8->setRestrictedCommittees([$this->getReference('committee-4')]);
        $manager->persist($delegatedAccess8);

        // access from senatorial candidate
        $delegatedAccess9 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_9));
        $delegatedAccess9->setDelegated($this->getReference('adherent-5')); // gisele-berthoux@caramail.com
        $delegatedAccess9->setDelegator($this->getReference('senatorial-candidate')); // senatorial-candidate@en-marche-dev.fr
        $delegatedAccess9->setRole('Candidat Sénateur délégué');
        $delegatedAccess9->setType('senatorial_candidate');
        $delegatedAccess9->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
            DelegatedAccess::ACCESS_MESSAGES,
        ]);
        $manager->persist($delegatedAccess9);

        // access for MyTeam members
        $members = [
            self::ACCESS_UUID_11 => 'my_team_member_1_1',
            self::ACCESS_UUID_12 => 'my_team_member_1_2',
            self::ACCESS_UUID_13 => 'my_team_member_2_1',
            self::ACCESS_UUID_14 => 'my_team_member_2_2',
            self::ACCESS_UUID_15 => 'my_team_member_3_1',
        ];
        foreach ($members as $uuid => $reference) {
            /** @var Member $member */
            $member = $this->getReference($reference);
            $team = $member->getTeam();
            $delegatedAccess = new DelegatedAccess(Uuid::fromString($uuid));
            $delegatedAccess->setDelegated($member->getAdherent());
            $delegatedAccess->setDelegator($team->getOwner());
            $delegatedAccess->setRole(RoleEnum::LABELS[$member->getRole()]);
            $delegatedAccess->setType($team->getScope());
            $delegatedAccess->setScopeFeatures($member->getScopeFeatures());
            $manager->persist($delegatedAccess);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadMyTeamData::class,
        ];
    }
}
