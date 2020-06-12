<?php

namespace App\DataFixtures\ORM;

use App\Entity\CommitteeCandidacy;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
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
    public const ELECTION_UUID3 = 'b81c3585-c802-48f6-9dca-19d1d4e08c44';
    public const ELECTION_UUID4 = '642ee8e6-916b-43b2-b0f9-8a821e5d9a2b';
    public const ELECTION_UUID5 = '13dd81bf-df09-487b-813d-8cbec95189aa';

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

    private function loadCommitteeAdherentElections(): void
    {
        $election = new Election(
            $this->getReference('designation-1'),
            Uuid::fromString(self::ELECTION_UUID1),
            [new ElectionRound()]
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-6')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $this->loadVoters($election);

        $this->manager->persist($election);

        $election = new Election(
            $this->getReference('designation-2'),
            Uuid::fromString(self::ELECTION_UUID2),
            [$round = new ElectionRound()]
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-5')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        $this->manager->persist($election);

        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID3),
            [$round = new ElectionRound()]
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-4')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        $this->manager->persist($election);

        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID4),
            [$round = new ElectionRound()]
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        $this->manager->persist($election);

        $election = new Election(
            $this->getReference('designation-4'),
            Uuid::fromString(self::ELECTION_UUID4),
            [$round = new ElectionRound()]
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        $this->manager->persist($election);

        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID5),
            [new ElectionRound(false), $round = new ElectionRound()]
        );

        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-3')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        $this->manager->persist($election);
    }

    private function loadCommitteeAdherentElectionCandidates(Election $election): void
    {
        $currentRound = $election->getCurrentRound();

        $womanPool = new ElectionPool('Femme');
        $manPool = new ElectionPool('Homme');

        $currentRound->addElectionPool($womanPool);
        $currentRound->addElectionPool($manPool);

        $election->addElectionPool($womanPool);
        $election->addElectionPool($manPool);

        foreach ($this->getCandidates() as $candidate) {
            $group = new CandidateGroup();
            $group->addCandidate(clone $candidate);

            if ($candidate->isWoman()) {
                $womanPool->addCandidateGroup($group);
            } else {
                $manPool->addCandidateGroup($group);
            }
        }
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

    private function loadVoters(Election $election): VotersList
    {
        $adherent1 = $this->getReference('assessor-1');
        $adherent2 = $this->getReference('adherent-20');

        $list = new VotersList($election);
        $list->addVoter($this->voters[$adherent1->getId()] ?? $this->voters[$adherent1->getId()] = new Voter($adherent1));
        $list->addVoter($this->voters[$adherent2->getId()] ?? $this->voters[$adherent2->getId()] = new Voter($adherent2));

        for ($i = 1; $i <= 9; ++$i) {
            $list->addVoter(new Voter());
        }

        $this->manager->persist($list);

        return $list;
    }

    private function loadResults(ElectionRound $electionRound, VotersList $votersList): void
    {
        $pools = $electionRound->getElectionPools();
        $counters = [];

        foreach ($votersList->getVoters() as $i => $voter) {
            // simulate abstention
            if (0 === $i % 7 || \in_array($voter, $this->voters, true)) {
                continue;
            }

            $result = new VoteResult($electionRound, VoteResult::generateVoterKey());

            foreach ($pools as $y => $pool) {
                $candidateGroups = $pool->getCandidateGroups();
                $choice = new VoteChoice($pool);

                if (0 === $i % rand(1, 10)) {
                    $choice->setIsBlank(true);
                } else {
                    $choice->setCandidateGroup($candidateGroups[$index = array_rand($candidateGroups, 1)]);
                    !isset($counters[$pool->getId()][$index]) ? $counters[$pool->getId()][$index] = 1 : ++$counters[$pool->getId()][$index];
                }

                $result->addVoteChoice($choice);
            }

            $this->manager->persist(new Vote($voter, $electionRound));
            $this->manager->persist($result);
        }

        // Mark elected groups
        foreach ($pools as $y => $pool) {
            $candidateGroups = $pool->getCandidateGroups();

            arsort($counters[$pool->getId()]);

            $key = array_search(max($counters[$pool->getId()]), $counters[$pool->getId()]);
            $candidateGroups[$key]->setElected(true);
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
}
