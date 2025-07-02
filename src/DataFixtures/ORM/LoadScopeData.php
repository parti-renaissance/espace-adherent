<?php

namespace App\DataFixtures\ORM;

use App\Entity\Scope;
use App\Scope\AppEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        FeatureEnum::REFERRALS,
    ];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (ScopeEnum::ALL as $code) {
            $manager->persist($this->createScope($code, \in_array($code, [ScopeEnum::PHONING, ScopeEnum::PAP]) ? [AppEnum::JEMARCHE] : [AppEnum::DATA_CORNER]));
        }

        $manager->flush();
    }

    private function createScope(string $code, array $apps = [AppEnum::DATA_CORNER]): Scope
    {
        $scope = new Scope($code, $this->translator->trans('role.'.$code, ['gender' => 'male']), $this->getFeatures($code), $apps);

        if (ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY === $code) {
            $scope->canaryFeatures = [FeatureEnum::PUBLICATIONS];
        }

        $scope->colorPrimary = '#3A7DFF';
        $scope->colorSoft = '#E8F0FF';
        $scope->colorHover = '#2F6FE0';
        $scope->colorActive = '#1C5CD8';

        return $scope;
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
            ScopeEnum::LEGISLATIVE_CANDIDATE => array_merge(self::BASIC_FEATURES, [FeatureEnum::NEWS, FeatureEnum::PAP, FeatureEnum::MY_TEAM, FeatureEnum::PAP_V2, FeatureEnum::PROCURATIONS]),
            ScopeEnum::REGIONAL_COORDINATOR => array_diff(FeatureEnum::ALL, [FeatureEnum::DEPARTMENT_SITE]),
            ScopeEnum::PROCURATIONS_MANAGER => [FeatureEnum::PROCURATIONS],
            ScopeEnum::AGORA_PRESIDENT => [FeatureEnum::AGORAS, FeatureEnum::CONTACTS, FeatureEnum::EVENTS],
            ScopeEnum::AGORA_GENERAL_SECRETARY => [FeatureEnum::AGORAS, FeatureEnum::CONTACTS, FeatureEnum::EVENTS],
            default => FeatureEnum::ALL,
        };
    }
}
