<?php

namespace App\DataFixtures\ORM;

use App\Entity\Scope;
use App\Scope\AppEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadScopeData extends Fixture
{
    private const BASIC_FEATURES = [
        FeatureEnum::DASHBOARD,
        FeatureEnum::CONTACTS,
        FeatureEnum::MESSAGES,
        FeatureEnum::EVENTS,
        FeatureEnum::MOBILE_APP,
        FeatureEnum::ELECTIONS,
        FeatureEnum::RIPOSTES,
    ];

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createScope(ScopeEnum::REFERENT, 'Référent'));
        $manager->persist($this->createScope(ScopeEnum::DEPUTY, 'Député', self::BASIC_FEATURES));
        $manager->persist($this->createScope(ScopeEnum::SENATOR, 'Sénateur', self::BASIC_FEATURES));
        $manager->persist($this->createScope(ScopeEnum::NATIONAL, 'National'));
        $manager->persist($this->createScope(ScopeEnum::NATIONAL_COMMUNICATION, 'National communication', [FeatureEnum::MESSAGES, FeatureEnum::NEWS]));
        $manager->persist($this->createScope(ScopeEnum::CANDIDATE, 'Candidat', self::BASIC_FEATURES, []));
        $manager->persist($this->createScope(ScopeEnum::PHONING, 'Appelant', [], [AppEnum::JEMARCHE]));
        $manager->persist($this->createScope(ScopeEnum::PHONING_NATIONAL_MANAGER, 'Responsable Phoning', [FeatureEnum::TEAM, FeatureEnum::PHONING_CAMPAIGN]));
        $manager->persist($this->createScope(ScopeEnum::PAP_NATIONAL_MANAGER, 'Responsable National PAP', [FeatureEnum::PAP]));

        $manager->flush();
    }

    private function createScope(
        string $code,
        string $name,
        array $features = FeatureEnum::ALL,
        array $apps = [AppEnum::DATA_CORNER]
    ): Scope {
        return new Scope($code, $name, $features, $apps);
    }
}
