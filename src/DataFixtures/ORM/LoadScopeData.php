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
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createScope(ScopeEnum::REFERENT, 'Référent'));
        $manager->persist($this->createScope(ScopeEnum::DEPUTY, 'Député'));
        $manager->persist($this->createScope(ScopeEnum::SENATOR, 'Sénateur'));
        $manager->persist($this->createScope(ScopeEnum::NATIONAL, 'National'));
        $manager->persist($this->createScope(ScopeEnum::CANDIDATE, 'Candidat', FeatureEnum::ALL, []));
        $manager->persist($this->createScope(ScopeEnum::PHONING, 'Appelant', [], [AppEnum::JEMARCHE]));

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
