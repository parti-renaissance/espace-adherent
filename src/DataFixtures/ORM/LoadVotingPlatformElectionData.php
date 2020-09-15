<?php

namespace App\DataFixtures\ORM;

use App\Entity\CommitteeCandidacy;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionPoolCodeEnum;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\VoteChoice;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VoteResult;
use App\Entity\VotingPlatform\VotersList;
use App\ValueObject\Genders;
use App\VotingPlatform\Election\ResultCalculator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

class LoadVotingPlatformElectionData extends Fixture implements DependentFixtureInterface
{
    public const ELECTION_UUID1 = 'd678c30a-a94b-4ecf-8cfc-0e06d1fb16df';
    public const ELECTION_UUID2 = '278ec098-e5f2-45e3-9faf-a9b2cb9305fd';
    public const ELECTION_UUID3 = 'b81c3585-c802-48f6-9dca-19d1d4e08c44';
    public const ELECTION_UUID4 = '642ee8e6-916b-43b2-b0f9-8a821e5d9a2b';
    public const ELECTION_UUID5 = '13dd81bf-df09-487b-813d-8cbec95189aa';
    public const ELECTION_UUID6 = '071a3abc-fb4c-43c0-91b9-7cbeb1e02b92';
    public const ELECTION_UUID7 = 'b58e5538-c6e7-10a4-88c3-de59305e61a8';
    public const ELECTION_UUID8 = '138140c6-1dd2-11b2-b23f-2b71345a2be1';

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
        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-1'),
            Uuid::fromString(self::ELECTION_UUID1),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-6')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $this->loadVoters($election);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-2'),
            Uuid::fromString(self::ELECTION_UUID2),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-5')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID3),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-4')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        // -------------------------------------------

        // Election with started second round
        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID6),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID4),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->close();

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-4'),
            Uuid::fromString(self::ELECTION_UUID7),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->close();

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-3'),
            Uuid::fromString(self::ELECTION_UUID5),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-3')));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->startSecondRound($round->getElectionPools());

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-7'),
            Uuid::fromString(self::ELECTION_UUID8),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity(null, $coTerr = $this->getReference('coTerr_92')));
        $this->loadTerritorialCouncilElectionCandidates($election, $coTerr);

        $this->manager->flush();
    }

    private function loadCommitteeAdherentElectionCandidates(Election $election): void
    {
        $currentRound = $election->getCurrentRound();

        $femalePool = new ElectionPool(ElectionPoolCodeEnum::FEMALE);
        $malePool = new ElectionPool(ElectionPoolCodeEnum::MALE);

        $currentRound->addElectionPool($femalePool);
        $currentRound->addElectionPool($malePool);

        $election->addElectionPool($femalePool);
        $election->addElectionPool($malePool);

        foreach ($this->getCandidates() as $candidate) {
            $group = new CandidateGroup();
            $group->addCandidate(clone $candidate);

            if ($candidate->isFemale()) {
                $femalePool->addCandidateGroup($group);
            } else {
                $malePool->addCandidateGroup($group);
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

        array_map(function (Candidate $candidate) use ($committeeCandidacy) {
            if (rand(0, 1)) {
                $candidate->setImagePath($committeeCandidacy->getImagePath());
            }
        }, $candidates);

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
                $totalGroups = \count($candidateGroups);

                $choice = new VoteChoice($pool);

                if (0 === $i % 10) {
                    $choice->setIsBlank(true);
                } else {
                    $choice->setCandidateGroup($candidateGroups[$index = rand(1, rand(1, $totalGroups) - 1)]);
                    !isset($counters[$pool->getId()][$index]) ? $counters[$pool->getId()][$index] = 1 : ++$counters[$pool->getId()][$index];
                }

                $result->addVoteChoice($choice);
            }

            $this->manager->persist(new Vote($voter, $electionRound));
            $this->manager->persist($result);
        }

        $this->manager->flush();

        $this->getResultCalculator()->computeElectionResult($electionRound->getElection());
    }

    private function loadTerritorialCouncilElectionCandidates(
        Election $election,
        TerritorialCouncil $territorialCouncil
    ): void {
        $currentElection = $territorialCouncil->getCurrentElection();
        /** @var TerritorialCouncilMembership[] $memberships */
        $memberships = $territorialCouncil->getMemberships()->toArray();
        $voterList = new VotersList($election);
        $this->manager->persist($voterList);
        $pools = [];

        foreach ($memberships as $membership) {
            $voterList->addVoter(new Voter($membership->getAdherent()));

            if ($candidacy = $membership->getCandidacyForElection($currentElection)) {
                if ($candidacy->isConfirmed()) {
                    $pools[$candidacy->getQuality()][] = $candidacy;
                }
            }
        }

        $currentRound = $election->getCurrentRound();

        foreach ($pools as $key => $candidacies) {
            $pool = new ElectionPool($key);

            foreach ($candidacies as $candidacy) {
                /** @var Candidacy $candidacy */
                if ($candidacy->isTaken()) {
                    continue;
                }

                $group = new CandidateGroup();
                $adherent = $candidacy->getMembership()->getAdherent();
                $candidate = new Candidate($adherent->getFirstName(), $adherent->getLastName(), $candidacy->getGender(), $adherent);
                $candidate->setImagePath($candidacy->getImagePath());
                $candidate->setFaithStatement($candidacy->getFaithStatement());
                $candidate->setBiography($candidacy->getBiography());
                $group->addCandidate($candidate);
                $candidacy->take();

                if ($binome = $candidacy->getBinome()) {
                    $adherent = $binome->getMembership()->getAdherent();
                    $candidate = new Candidate($adherent->getFirstName(), $adherent->getLastName(), $binome->getGender(), $adherent);
                    $candidate->setImagePath($binome->getImagePath());
                    $candidate->setFaithStatement($binome->getFaithStatement());
                    $candidate->setBiography($binome->getBiography());
                    $group->addCandidate($candidate);
                    $binome->take();
                }

                $pool->addCandidateGroup($group);
            }

            $currentRound->addElectionPool($pool);
            $election->addElectionPool($pool);
        }
    }

    private function getResultCalculator(): ResultCalculator
    {
        return $this->container->get(ResultCalculator::class);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCommitteeCandidacyData::class,
            LoadDesignationData::class,
            LoadTerritorialCouncilCandidacyData::class,
        ];
    }
}
