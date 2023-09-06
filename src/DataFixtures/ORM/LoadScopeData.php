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
        FeatureEnum::SURVEY,
        FeatureEnum::ELECTED_REPRESENTATIVE,
        FeatureEnum::ADHERENT_FORMATIONS,
        FeatureEnum::GENERAL_MEETING_REPORTS,
        FeatureEnum::DOCUMENTS,
    ];

    private const LABELS = [
        ScopeEnum::REFERENT => 'Référent',
        ScopeEnum::DEPUTY => 'Délégué de circonscription',
        ScopeEnum::SENATOR => 'Sénateur',
        ScopeEnum::NATIONAL => 'National',
        ScopeEnum::NATIONAL_COMMUNICATION => 'National communication',
        ScopeEnum::CANDIDATE => 'Candidat',
        ScopeEnum::PHONING => 'Appelant',
        ScopeEnum::PHONING_NATIONAL_MANAGER => 'Responsable Phoning',
        ScopeEnum::PAP_NATIONAL_MANAGER => 'Responsable National PAP',
        ScopeEnum::PAP => 'Porte-à-porteur',
        ScopeEnum::CORRESPONDENT => 'Correspondant',
        ScopeEnum::LEGISLATIVE_CANDIDATE => 'Candidat aux législatives',
        ScopeEnum::REGIONAL_COORDINATOR => 'Coordinateur régional',
        ScopeEnum::REGIONAL_DELEGATE => 'Délégué régional',
        ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY => 'Président assemblée départementale',
        ScopeEnum::ANIMATOR => 'Responsable comité local',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (ScopeEnum::ALL as $code) {
            $manager->persist($this->createScope($code, \in_array($code, [ScopeEnum::PHONING, ScopeEnum::PAP]) ? [AppEnum::JEMARCHE] : [AppEnum::DATA_CORNER]));
        }

        $manager->flush();
    }

    private function createScope(string $code, array $apps = [AppEnum::DATA_CORNER]): Scope
    {
        return new Scope($code, self::LABELS[$code] ?? $code, $this->getFeatures($code), $apps);
    }

    private function getFeatures(string $scopeCode): array
    {
        return match ($scopeCode) {
            ScopeEnum::DEPUTY,
            ScopeEnum::SENATOR => self::BASIC_FEATURES,
            ScopeEnum::NATIONAL => array_diff(FeatureEnum::ALL, [FeatureEnum::MESSAGES, FeatureEnum::DEPARTMENT_SITE, FeatureEnum::ELECTED_REPRESENTATIVE]),
            ScopeEnum::NATIONAL_COMMUNICATION => [FeatureEnum::NEWS],
            ScopeEnum::CANDIDATE => array_merge(self::BASIC_FEATURES, [FeatureEnum::PAP]),
            ScopeEnum::PAP,
            ScopeEnum::PHONING => [],
            ScopeEnum::PHONING_NATIONAL_MANAGER => [FeatureEnum::TEAM, FeatureEnum::PHONING_CAMPAIGN],
            ScopeEnum::PAP_NATIONAL_MANAGER => [FeatureEnum::PAP],
            ScopeEnum::CORRESPONDENT => array_merge(self::BASIC_FEATURES, [FeatureEnum::NEWS, FeatureEnum::MY_TEAM]),
            ScopeEnum::LEGISLATIVE_CANDIDATE => array_merge(self::BASIC_FEATURES, [FeatureEnum::NEWS, FeatureEnum::PAP, FeatureEnum::MY_TEAM, FeatureEnum::PAP_V2]),
            ScopeEnum::REGIONAL_COORDINATOR => array_diff(FeatureEnum::ALL, [FeatureEnum::DEPARTMENT_SITE]),

            default => FeatureEnum::ALL,
        };
    }
}
