<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\MyTeam\RoleEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
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
    public const ACCESS_UUID_10 = '13208e84-450d-4d44-86e7-fb5f0b7e642c';
    public const ACCESS_UUID_11 = 'ef339f8e-e9d0-4f22-b98f-1a7526246cad';
    public const ACCESS_UUID_12 = '433e368f-fd4e-4a24-9f01-b667f8e3b9f2';
    public const ACCESS_UUID_13 = '6d2506a7-bec7-45a1-a5ee-8f8b48daa5ec';
    public const ACCESS_UUID_14 = '2c6134f7-4312-45c4-9ab7-89f2b0731f86';
    public const ACCESS_UUID_15 = '689757d2-dea5-49d1-95fe-281fc860ff77';
    public const ACCESS_UUID_16 = 'b24fea43-ecd8-4bf4-b500-6f97886ab77c';
    public const ACCESS_UUID_17 = '1d29b80c-a308-441c-9d7d-a333c366fdb1';
    public const ACCESS_UUID_18 = 'fab73b77-1470-4e93-a1ff-85b649f8fb72';
    public const ACCESS_UUID_19 = '4e1eddaf-00e3-4670-aa11-24420da834c4';
    public const ACCESS_UUID_20 = '72ee9c01-7966-43d7-91bb-49e3061fda01';

    public function load(ObjectManager $manager): void
    {
        // full access, no committees or cities restriction
        $delegatedAccess1 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_1));
        $delegatedAccess1->setDelegated($this->getReference('adherent-8', Adherent::class)); // referent@en-marche-dev.fr
        $delegatedAccess1->setDelegator($this->getReference('deputy-ch-li', Adherent::class)); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess1->setRole(RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER]);
        $delegatedAccess1->setType(ScopeEnum::DEPUTY);
        $delegatedAccess1->setAccesses(DelegatedAccess::ACCESSES);

        $manager->persist($delegatedAccess1);

        // access to 2 tabs, no committees or cities restriction
        $delegatedAccess2 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_2));
        $delegatedAccess2->setDelegated($this->getReference('adherent-4', Adherent::class)); // luciole1989@spambox.fr
        $delegatedAccess2->setDelegator($this->getReference('deputy-ch-li', Adherent::class)); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess2->setRole(RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER]);
        $delegatedAccess2->setType(ScopeEnum::DEPUTY);
        $delegatedAccess2->setAccesses([DelegatedAccess::ACCESS_EVENTS, DelegatedAccess::ACCESS_ADHERENTS]);

        $manager->persist($delegatedAccess2);

        // access to 2 tabs, with cities restriction
        $delegatedAccess3 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_3));
        $delegatedAccess3->setDelegated($this->getReference('adherent-5', Adherent::class)); // gisele-berthoux@caramail.com
        $delegatedAccess3->setDelegator($this->getReference('deputy-ch-li', Adherent::class)); // deputy-ch-li@en-marche-dev.fr
        $delegatedAccess3->setRole(RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER]);
        $delegatedAccess3->setType(ScopeEnum::DEPUTY);
        $delegatedAccess3->setAccesses([DelegatedAccess::ACCESS_MESSAGES, DelegatedAccess::ACCESS_EVENTS]);
        $delegatedAccess3->setScopeFeatures([FeatureEnum::MESSAGES, FeatureEnum::EVENTS]);

        $manager->persist($delegatedAccess3);

        // second access to same user, but of type senator, with different accesses
        $delegatedAccess4 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_4));
        $delegatedAccess4->setDelegated($this->getReference('adherent-5', Adherent::class)); // gisele-berthoux@caramail.com
        $delegatedAccess4->setDelegator($this->getReference('senator-59', Adherent::class)); // senateur@en-marche-dev.fr
        $delegatedAccess4->setRole(RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER]);
        $delegatedAccess4->setType(ScopeEnum::SENATOR);
        $delegatedAccess4->setAccesses([DelegatedAccess::ACCESS_MESSAGES, DelegatedAccess::ACCESS_ADHERENTS]);
        $delegatedAccess4->setScopeFeatures([FeatureEnum::MESSAGES, FeatureEnum::CONTACTS]);

        $manager->persist($delegatedAccess4);

        // second deputy access to same user, with different accesses
        $delegatedAccess5 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_5));
        $delegatedAccess5->setDelegated($this->getReference('adherent-5', Adherent::class)); // gisele-berthoux@caramail.com
        $delegatedAccess5->setDelegator($this->getReference('deputy-75-2', Adherent::class)); // deputy-75-2@en-marche-dev.fr
        $delegatedAccess5->setRole(RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER]);
        $delegatedAccess5->setType(ScopeEnum::DEPUTY);
        $delegatedAccess5->setAccesses([DelegatedAccess::ACCESS_ADHERENTS]);
        $delegatedAccess5->setScopeFeatures([FeatureEnum::CONTACTS]);

        $manager->persist($delegatedAccess5);

        // no accesses
        $delegatedAccess6 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_6));
        $delegatedAccess6->setDelegated($this->getReference('adherent-8', Adherent::class)); // referent@en-marche-dev.fr
        $delegatedAccess6->setDelegator($this->getReference('senator-59', Adherent::class)); // senateur@en-marche-dev.fr
        $delegatedAccess6->setRole(RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER]);
        $delegatedAccess6->setType(ScopeEnum::SENATOR);

        $manager->persist($delegatedAccess6);

        // access from referent
        $delegatedAccess7 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_7));
        $delegatedAccess7->setDelegated($this->getReference('senator-59', Adherent::class)); // senateur@en-marche-dev.fr
        $delegatedAccess7->setDelegator($this->getReference('adherent-8', Adherent::class));
        $delegatedAccess7->setRole(RoleEnum::LABELS[RoleEnum::MOBILIZATION_MANAGER]);
        $delegatedAccess7->setType(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $delegatedAccess7->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
            DelegatedAccess::ACCESS_MESSAGES,
        ]);
        $delegatedAccess7->setScopeFeatures(FeatureEnum::ALL);

        $manager->persist($delegatedAccess7);

        $delegatedAccess10 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_10));
        $delegatedAccess10->setDelegated($this->getReference('municipal-manager-lille', Adherent::class)); // jean-claude.dusse@example.fr
        $delegatedAccess10->setDelegator($this->getReference('adherent-8', Adherent::class)); // referent@en-marche-dev.fr
        $delegatedAccess10->setRole('Responsable Digital');
        $delegatedAccess10->setType('referent');
        $delegatedAccess10->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
        ]);

        $manager->persist($delegatedAccess10);

        // access from candidate
        $delegatedAccess8 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_8));
        $delegatedAccess8->setDelegated($this->getReference('adherent-5', Adherent::class)); // gisele-berthoux@caramail.com
        $delegatedAccess8->setDelegator($this->getReference('adherent-3', Adherent::class)); // jacques.picard@en-marche.fr
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
        $manager->persist($delegatedAccess8);

        $delegatedAccess10 = new DelegatedAccess(Uuid::uuid4());
        $delegatedAccess10->setDelegated($this->getReference('deputy-75-1', Adherent::class));
        $delegatedAccess10->setDelegator($this->getReference('president-ad-1', Adherent::class));
        $delegatedAccess10->setRole('Responsable communication');
        $delegatedAccess10->setType(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $delegatedAccess10->setAccesses([
            DelegatedAccess::ACCESS_ADHERENTS,
            DelegatedAccess::ACCESS_MESSAGES,
        ]);
        $manager->persist($delegatedAccess10);

        // access from PAD
        $delegatedAccess17 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_17));
        $delegatedAccess17->setDelegated($this->getReference('adherent-5', Adherent::class)); // gisele-berthoux@caramail.com
        $delegatedAccess17->setDelegator($this->getReference('president-ad-1', Adherent::class)); // president-ad@renaissance-dev.fr
        $delegatedAccess17->setRole('Responsable élus délégué #1');
        $delegatedAccess17->setType('president_departmental_assembly');
        $delegatedAccess17->setScopeFeatures([
            FeatureEnum::ELECTED_REPRESENTATIVE,
        ]);
        $manager->persist($delegatedAccess17);

        $delegatedAccess18 = new DelegatedAccess(Uuid::fromString(self::ACCESS_UUID_18));
        $delegatedAccess18->setDelegated($this->getReference('municipal-manager-lille', Adherent::class)); // jean-claude.dusse@example.fr
        $delegatedAccess18->setDelegator($this->getReference('president-ad-1', Adherent::class)); // president-ad@renaissance-dev.fr
        $delegatedAccess18->setRole('Responsable élus délégué #2');
        $delegatedAccess18->setType('president_departmental_assembly');
        $delegatedAccess18->setScopeFeatures([
            FeatureEnum::ELECTED_REPRESENTATIVE,
        ]);
        $manager->persist($delegatedAccess18);

        // access for MyTeam members
        $members = [
            self::ACCESS_UUID_11 => 'my_team_member_1_1',
            self::ACCESS_UUID_12 => 'my_team_member_1_2',
            self::ACCESS_UUID_13 => 'my_team_member_2_1',
            self::ACCESS_UUID_14 => 'my_team_member_2_2',
            self::ACCESS_UUID_15 => 'my_team_member_3_1',
            self::ACCESS_UUID_16 => 'my_team_lc_member_1',
            self::ACCESS_UUID_19 => 'my_team_pad_member_1',
            self::ACCESS_UUID_20 => 'my_team_pad_member_2',
        ];
        foreach ($members as $uuid => $reference) {
            /** @var Member $member */
            $member = $this->getReference($reference, Member::class);
            $team = $member->getTeam();
            $delegatedAccess = new DelegatedAccess(Uuid::fromString($uuid));
            $delegatedAccess->setDelegated($member->getAdherent());
            $delegatedAccess->setDelegator($team->getOwner());
            $delegatedAccess->setRole(RoleEnum::LABELS[$member->getRole()]);
            $delegatedAccess->roleCode = $member->getRole();
            $delegatedAccess->setType($team->getScope());
            $delegatedAccess->setScopeFeatures($member->getScopeFeatures());
            $manager->persist($delegatedAccess);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadMyTeamData::class,
        ];
    }
}
