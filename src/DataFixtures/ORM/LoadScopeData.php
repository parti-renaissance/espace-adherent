<?php

namespace App\DataFixtures\ORM;

use App\Entity\Scope;
use App\Scope\AppEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadScopeData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createScope(ScopeEnum::CANDIDATE, 'Candidat'));
        $manager->persist($this->createScope(ScopeEnum::REFERENT, 'Référent'));
        $manager->persist($this->createScope(ScopeEnum::DEPUTY, 'Député'));
        $manager->persist($this->createScope(ScopeEnum::SENATOR, 'Sénateur'));
        $manager->persist($this->createScope(ScopeEnum::NATIONAL, 'National'));

        $manager->flush();
    }

    private function createScope(
        string $code,
        string $name,
        array $features = FeatureEnum::ALL,
        array $apps = AppEnum::ALL
    ): Scope {
        return new Scope($code, $name, $features, $apps);
    }
}
