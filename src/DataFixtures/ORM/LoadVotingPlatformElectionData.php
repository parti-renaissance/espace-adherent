<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\LocalElection\LocalElection;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
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
use App\Repository\CommitteeMembershipRepository;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\ResultCalculator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
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
    public const ELECTION_UUID9 = '39949c8f-d233-1e20-905e-5e214c6a12f2';
    public const ELECTION_UUID10 = '4095339f-3aad-18ba-924f-adffe2085cd6';
    public const ELECTION_UUID11 = '13814072-1dd2-11b2-9593-b97d988be702';
    public const ELECTION_UUID12 = 'f9f2894b-64a2-446f-b2fd-134015f0c0d2';
    public const ELECTION_UUID13 = '85f851dd-ea60-4c93-a505-fb51d41c7d70';

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var ObjectManager
     */
    private $manager;
    private $voters = [];

    public function __construct(
        private readonly ResultCalculator $resultCalculator,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');
        $this->manager = $manager;

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-1', Designation::class),
            Uuid::fromString(self::ELECTION_UUID1),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-6', Committee::class)));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $this->loadVoters($election);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-2', Designation::class),
            Uuid::fromString(self::ELECTION_UUID2),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-5', Committee::class)));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $this->loadVoters($election);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-3', Designation::class),
            Uuid::fromString(self::ELECTION_UUID3),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-4', Committee::class)));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-3', Designation::class),
            Uuid::fromString(self::ELECTION_UUID4),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1', Committee::class)));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->close();

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-4', Designation::class),
            Uuid::fromString(self::ELECTION_UUID7),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-1', Committee::class)));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->close();

        // -------------------------------------------

        // Election with started second round
        $election = new Election(
            $this->getReference('designation-3', Designation::class),
            Uuid::fromString(self::ELECTION_UUID5),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-3', Committee::class)));

        $this->loadCommitteeAdherentElectionCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);
        $election->startSecondRound($round->getElectionPools());

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-8', Designation::class),
            Uuid::fromString(self::ELECTION_UUID9),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-13', Committee::class)));
        $this->loadCommitteeSupervisorElectionCandidates($election);
        $this->manager->persist($this->loadCommitteeSupervisorElectionVoters($election));

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-8', Designation::class),
            Uuid::fromString(self::ELECTION_UUID10),
            [new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-14', Committee::class)));
        $this->loadCommitteeSupervisorElectionCandidates($election);
        $this->manager->persist($this->loadCommitteeSupervisorElectionVoters($election));

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-9', Designation::class),
            Uuid::fromString(self::ELECTION_UUID11),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-15', Committee::class)));
        $this->loadCommitteeSupervisorElectionCandidates($election);
        $this->manager->persist($votersList = $this->loadCommitteeSupervisorElectionVoters($election));
        $this->loadResults($round, $votersList, false);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-13', Designation::class),
            Uuid::fromString(self::ELECTION_UUID6),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $this->loadLocalElectionCandidates($election, $this->getReference('local-election-1', LocalElection::class));
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-14', Designation::class),
            Uuid::fromString(self::ELECTION_UUID12),
            [$round = new ElectionRound()]
        );
        $this->manager->persist($election);
        $this->loadLocalPollCandidates($election);
        $votersList = $this->loadVoters($election);
        $this->loadResults($round, $votersList);

        // -------------------------------------------

        $election = new Election(
            $this->getReference('designation-committee-01', Designation::class),
            Uuid::fromString(self::ELECTION_UUID13),
            [new ElectionRound()]
        );
        $election->setElectionEntity(new ElectionEntity($this->getReference('committee-v2-1', Committee::class)));
        $this->manager->persist($election);
        $this->loadCommitteeSupervisorElectionCandidates($election);
        $this->manager->persist($this->loadCommitteeSupervisorElectionVoters($election));

        // -------------------------------------------

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

    private function loadCommitteeSupervisorElectionCandidates(Election $election): void
    {
        $currentRound = $election->getCurrentRound();

        $pool = new ElectionPool(ElectionPoolCodeEnum::COMMITTEE_SUPERVISOR);
        $currentRound->addElectionPool($pool);
        $election->addElectionPool($pool);

        $committee = $election->getElectionEntity()->getCommittee();
        /** @var CommitteeElection $committeeElection */
        $committeeElection = $committee->getCurrentElection();

        foreach ($committeeElection->getCandidaciesGroups() as $candidacyGroup) {
            $pool->addCandidateGroup($group = new CandidateGroup());

            foreach ($candidacyGroup->getCandidacies() as $committeeCandidacy) {
                $group->addCandidate($candidate = new Candidate(
                    $committeeCandidacy->getFirstName(),
                    $committeeCandidacy->getLastName(),
                    $committeeCandidacy->getGender(),
                    $committeeCandidacy->getAdherent()
                ));
                $candidate->setImagePath($committeeCandidacy->getImagePath());
                $candidate->setBiography($committeeCandidacy->getBiography());
                $candidate->setFaithStatement($committeeCandidacy->getFaithStatement());
            }
        }
    }

    private function loadCommitteeSupervisorElectionVoters(Election $election): VotersList
    {
        $list = new VotersList($election);

        foreach ($election->getElectionPools() as $pool) {
            foreach ($pool->getCandidateGroups() as $candidateGroup) {
                foreach ($candidateGroup->getCandidates() as $candidate) {
                    $adherent = $candidate->getAdherent();
                    $list->addVoter($this->voters[$adherent->getId()] ?? $this->voters[$adherent->getId()] = new Voter($adherent));
                }
            }
        }

        return $list;
    }

    /**
     * @return Candidate[]
     */
    private function getCandidates(): array
    {
        $candidates = [
            $candidate1 = new Candidate($this->faker->firstNameFemale(), $this->faker->lastName(), Genders::FEMALE, $this->getReference('adherent-1', Adherent::class)),
            new Candidate($this->faker->firstNameFemale(), $this->faker->lastName(), Genders::FEMALE, $this->getReference('adherent-2', Adherent::class)),
            new Candidate($this->faker->firstNameFemale(), $this->faker->lastName(), Genders::FEMALE, $this->getReference('adherent-3', Adherent::class)),
            new Candidate($this->faker->firstNameFemale(), $this->faker->lastName(), Genders::FEMALE, $this->getReference('adherent-4', Adherent::class)),
            new Candidate($this->faker->firstNameFemale(), $this->faker->lastName(), Genders::FEMALE, $this->getReference('adherent-5', Adherent::class)),
            new Candidate($this->faker->firstNameMale(), $this->faker->lastName(), Genders::MALE, $this->getReference('adherent-6', Adherent::class)),
            $candidate2 = new Candidate($this->faker->firstNameMale(), $this->faker->lastName(), Genders::MALE, $this->getReference('adherent-7', Adherent::class)),
            new Candidate($this->faker->firstNameMale(), $this->faker->lastName(), Genders::MALE, $this->getReference('adherent-8', Adherent::class)),
            new Candidate($this->faker->firstNameMale(), $this->faker->lastName(), Genders::MALE, $this->getReference('adherent-9', Adherent::class)),
            new Candidate($this->faker->firstNameMale(), $this->faker->lastName(), Genders::MALE, $this->getReference('adherent-10', Adherent::class)),
        ];

        $candidate1->setBiography($this->faker->paragraph(10));
        $candidate2->setBiography($this->faker->paragraph(10));

        shuffle($candidates);

        return $candidates;
    }

    private function loadVoters(Election $election): VotersList
    {
        $adherent1 = $this->getReference('assessor-1', Adherent::class);
        $adherent2 = $this->getReference('adherent-20', Adherent::class);
        $adherent3 = $this->getReference('adherent-5', Adherent::class);

        $list = new VotersList($election);
        $list->addVoter($this->voters[$adherent1->getId()] ?? $this->voters[$adherent1->getId()] = new Voter($adherent1));
        $list->addVoter($this->voters[$adherent2->getId()] ?? $this->voters[$adherent2->getId()] = new Voter($adherent2));
        $list->addVoter($this->voters[$adherent3->getId()] ?? $this->voters[$adherent3->getId()] = new Voter($adherent3));

        if ($committee = $election->getElectionEntity()?->getCommittee()) {
            foreach ($this->committeeMembershipRepository->findCommitteeMemberships($committee) as $member) {
                $adherent = $member->getAdherent();
                $list->addVoter($this->voters[$adherent->getId()] ?? $this->voters[$adherent->getId()] = new Voter($adherent));
            }
        }

        for ($i = 1; $i <= 9; ++$i) {
            $list->addVoter(new Voter());
        }

        $this->manager->persist($list);

        return $list;
    }

    private function loadResults(ElectionRound $electionRound, VotersList $votersList, bool $random = true): void
    {
        $pools = $electionRound->getElectionPools();

        foreach ($votersList->getVoters() as $i => $voter) {
            // simulate abstention
            if (0 === $i % 7) {
                continue;
            }

            $result = new VoteResult($electionRound, VoteResult::generateVoterKey(), null);

            foreach ($pools as $pool) {
                $candidateGroups = $pool->getCandidateGroups();
                $totalGroups = \count($candidateGroups);

                $choice = new VoteChoice($pool);

                if (0 === $i % 10) {
                    $choice->setIsBlank(true);
                } else {
                    $choice->setCandidateGroup($candidateGroups[$random ? random_int(0, $totalGroups - 1) : $i % 2]);
                }

                $result->addVoteChoice($choice);
            }

            $this->manager->persist(new Vote($voter, $electionRound));
            $this->manager->persist($result);
        }

        $this->manager->flush();

        $this->resultCalculator->computeElectionResult($electionRound->getElection());
    }

    private function loadLocalElectionCandidates(Election $election, LocalElection $localElection): void
    {
        $pool = new ElectionPool(DesignationTypeEnum::LOCAL_ELECTION);
        $lists = $localElection->getCandidaciesGroups();
        $currentRound = $election->getCurrentRound();
        $currentRound->addElectionPool($pool);
        $election->addElectionPool($pool);

        foreach ($lists as $list) {
            $pool->addCandidateGroup($group = new CandidateGroup());

            if ($list->hasFaithStatementFile()) {
                $group->mediaFilePath = $list->getFaithStatementFilePath();
            }

            $candidacyCount = \count($list->getCandidacies());

            foreach ($list->getCandidacies() as $candidacy) {
                $group->addCandidate($candidate = new Candidate(
                    $candidacy->getFirstName(),
                    $candidacy->getLastName(),
                    $candidacy->getGender()
                ));
                $candidate->position = $candidacy->getPosition();
            }

            foreach ($list->getSubstituteCandidacies() as $substituteCandidacy) {
                $group->addCandidate($candidate = new Candidate(
                    $substituteCandidacy->getFirstName(),
                    $substituteCandidacy->getLastName(),
                    $substituteCandidacy->getGender()
                ));
                $candidate->position = $candidacyCount + $substituteCandidacy->getPosition();
                $candidate->isSubstitute = true;
            }
        }
    }

    private function loadLocalPollCandidates(Election $election): void
    {
        $designation = $election->getDesignation();
        $currentRound = $election->getCurrentRound();

        foreach ($designation->poll->getQuestions() as $question) {
            $pool = new ElectionPool($question->content);

            $currentRound->addElectionPool($pool);
            $election->addElectionPool($pool);

            foreach ($question->getChoices() as $choice) {
                $pool->addCandidateGroup($group = new CandidateGroup());
                $group->addCandidate(new Candidate($choice->label, '', ''));
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            LoadCommitteeV1Data::class,
            LoadCommitteeV1CandidacyData::class,
            LoadCommitteeV2CandidacyData::class,
            LoadDesignationData::class,
            LoadLocalElectionData::class,
        ];
    }
}
