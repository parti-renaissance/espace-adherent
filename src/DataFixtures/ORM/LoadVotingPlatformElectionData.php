<?php

namespace App\DataFixtures\ORM;

use App\Entity\CommitteeCandidacy;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\VoteChoice;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VoteResult;
use App\Entity\VotingPlatform\VotersList;
use App\ValueObject\Genders;
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
            $this->getReference('designation-2'),
            Uuid::fromString(self::ELECTION_UUID2)
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-5')));

        $this->loadCandidates($election);
        $this->loadVoters($election);
        $this->loadResults($election);

        $this->manager->persist($election);
    }

    private function loadVoters(Election $election): void
    {
        $adherent = $this->getReference('assessor-1');

        $list = new VotersList($election);
        $list->addVoter($this->voters[$adherent->getId()] ?? $this->voters[$adherent->getId()] = new Voter($adherent));

        $this->manager->persist($list);
    }

    private function loadResults(Election $election): void
    {
        $candidateGroups = $election->getCandidateGroups();

        $women = $candidateGroups->getWomanCandidateGroups();
        $men = $candidateGroups->getManCandidateGroups();

        $counters = [
            'women' => [],
            'men' => [],
        ];

        for ($i = 1; $i < 100; ++$i) {
            $result = new VoteResult($election, VoteResult::generateVoterKey());

            // WOMAN
            $choice = new VoteChoice();
            if (0 === $i % 10) {
                $choice->setIsBlank(true);
            } else {
                $choice->setCandidateGroup($women[$index = array_rand($women, 1)]);
                !isset($counters['women'][$index]) ? $counters['women'][$index] = 1 : ++$counters['women'][$index];
            }
            $result->addVoteChoice($choice);

            // MAN
            $choice = new VoteChoice();
            if (0 === $i % 15) {
                $choice->setIsBlank(true);
            } else {
                $choice->setCandidateGroup($men[$index = array_rand($men, 1)]);
                !isset($counters['men'][$index]) ? $counters['men'][$index] = 1 : ++$counters['men'][$index];
            }
            $result->addVoteChoice($choice);

            $this->manager->persist($result);
        }

        arsort($counters['women']);
        arsort($counters['men']);

        $women[key($counters['women'])]->setElected(true);
        $men[key($counters['men'])]->setElected(true);
    }
}
