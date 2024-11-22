<?php

namespace App\DataFixtures\ORM;

use App\Entity\VotingPlatform\Designation\CandidacyPool\CandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyPool\Candidacy;
use App\Entity\VotingPlatform\Designation\CandidacyPool\CandidacyPool;
use App\Entity\VotingPlatform\Designation\Designation;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDesignationCandidacyPoolData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($pool = new CandidacyPool());
        $pool->label = 'Listes pour l\'élection du bureau de l\'ADT';

        $pool->addCandidaciesGroup($group = new CandidaciesGroup());
        $group->label = 'Ensemble';
        $group->addCandidacy($candidacy = new Candidacy());
        $candidacy->setFirstName('Albert');
        $candidacy->setLastName('Dupont');
        $candidacy->setGender(Genders::MALE);
        $candidacy->setPosition(1);

        $group->addCandidacy($candidacy = new Candidacy());
        $candidacy->setFirstName('Marie');
        $candidacy->setLastName('Dupont');
        $candidacy->setGender(Genders::FEMALE);
        $candidacy->setPosition(2);

        $group->addCandidacy($candidacy = new Candidacy());
        $candidacy->setFirstName('Jack');
        $candidacy->setLastName('Dupont');
        $candidacy->setGender(Genders::MALE);
        $candidacy->setPosition(3);
        $candidacy->isSubstitute = true;

        $group->addCandidacy($candidacy = new Candidacy());
        $candidacy->setFirstName('Emma');
        $candidacy->setLastName('Dupont');
        $candidacy->setGender(Genders::FEMALE);
        $candidacy->setPosition(4);
        $candidacy->isSubstitute = true;

        $pool->addCandidaciesGroup($group = new CandidaciesGroup());
        $group->label = 'Avec Vous';
        $group->addCandidacy($candidacy = new Candidacy());
        $candidacy->setFirstName('Philippe');
        $candidacy->setLastName('Petit');
        $candidacy->setGender(Genders::MALE);
        $candidacy->setPosition(1);

        $group->addCandidacy($candidacy = new Candidacy());
        $candidacy->setFirstName('Sara');
        $candidacy->setLastName('Petit');
        $candidacy->setGender(Genders::FEMALE);
        $candidacy->setPosition(2);

        $group->addCandidacy($candidacy = new Candidacy());
        $candidacy->setFirstName('Remi');
        $candidacy->setLastName('Petit');
        $candidacy->setGender(Genders::MALE);
        $candidacy->setPosition(3);
        $candidacy->isSubstitute = true;

        /** @var Designation $designation */
        foreach ([
            $this->getReference('designation-16'),
            $this->getReference('designation-17'),
        ] as $designation) {
            $designation->addCandidacyPool($pool);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadDesignationData::class,
        ];
    }
}
