<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CommitteeCandidacy;
use AppBundle\Entity\VotingPlatform\Candidate;
use AppBundle\Entity\VotingPlatform\CandidateGroup;
use AppBundle\Entity\VotingPlatform\Election;
use AppBundle\Entity\VotingPlatform\ElectionEntity;
use AppBundle\Entity\VotingPlatform\Voter;
use AppBundle\Entity\VotingPlatform\VotersList;
use AppBundle\ValueObject\Genders;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

class LoadVotingPlatformElectionData extends AbstractFixture implements DependentFixtureInterface
{
    public const ELECTION_UUID1 = 'd678c30a-a94b-4ecf-8cfc-0e06d1fb16df';
    public const ELECTION_UUID2 = '278ec098-e5f2-45e3-9faf-a9b2cb9305fd';

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var ObjectManager
     */
    private $manager;
    private $voters = [];

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create('fr_FR');
        $this->manager = $manager;

        $this->loadCommitteeAdherentElections();

        $manager->flush();
    }

    private function loadCandidates(Election $election): void
    {
        foreach ($this->getCandidates() as $candidate) {
            $group = new CandidateGroup();
            $group->addCandidate(clone $candidate);

            $election->addCandidateGroup($group);
        }
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCommitteeCandidacyData::class,
            LoadDesignationData::class,
        ];
    }

    /**
     * @return Candidate[]
     */
    private function getCandidates(): array
    {
        $candidates = [
            $candidate1 = new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE),
            new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE),
            new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE),
            new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE),
            new Candidate($this->faker->firstNameFemale, $this->faker->lastName, Genders::FEMALE),
            new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE),
            $candidate2 = new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE),
            new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE),
            new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE),
            new Candidate($this->faker->firstNameMale, $this->faker->lastName, Genders::MALE),
        ];

        $candidate1->setBiography($this->faker->paragraph(10));
        $candidate2->setBiography($this->faker->paragraph(10));

        /** @var CommitteeCandidacy $committeeCandidacy */
        $committeeCandidacy = $this->getReference('committee-candidacy-1');
        $candidate1->setImagePath($committeeCandidacy->getImagePath());
        $candidate2->setImagePath($committeeCandidacy->getImagePath());

        shuffle($candidates);

        return $candidates;
    }

    private function loadCommitteeAdherentElections(): void
    {
        $election = new Election(
            $this->getReference('designation-1'),
            Uuid::fromString(self::ELECTION_UUID1)
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-6')));

        $this->loadCandidates($election);
        $this->loadVoters($election);

        $this->manager->persist($election);

        $election = new Election(
            $this->getReference('designation-1'),
            Uuid::fromString(self::ELECTION_UUID2)
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-5')));

        $this->loadCandidates($election);
        $this->loadVoters($election);

        $this->manager->persist($election);
    }

    private function loadVoters(Election $election): void
    {
        $adherent = $this->getReference('assessor-1');

        $list = new VotersList($election);
        $list->addVoter($this->voters[$adherent->getId()] ?? $this->voters[$adherent->getId()] = new Voter($adherent));

        $this->manager->persist($list);
    }
}
