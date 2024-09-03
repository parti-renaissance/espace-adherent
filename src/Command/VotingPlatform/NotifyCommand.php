<?php

namespace App\Command\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\VotersList;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\VotingPlatform\VotingPlatformResultsReadyMessage;
use App\Repository\CommitteeElectionRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Notifier\Event\CommitteeElectionCandidacyPeriodIsOverEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:voting-platform:notify',
    description: 'Voting Platform: send notification command',
)]
class NotifyCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DesignationRepository $designationRepository,
        private readonly CommitteeElectionRepository $committeeElectionRepository,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
        private readonly ElectionRepository $electionRepository,
        private readonly MailerService $transactionalMailer,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime();

        $this->notifyForEndForCandidacy($date);
        $this->notifyForForElectionResults($date);

        return self::SUCCESS;
    }

    private function notifyForEndForCandidacy(\DateTimeInterface $date): void
    {
        $designations = $this->designationRepository->getWithFinishCandidacyPeriod($date, [DesignationTypeEnum::COMMITTEE_ADHERENT]);

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
                $this->notifyCommitteeElections($designation);
            }
        }

        $this->io->progressFinish();
    }

    public function notifyCommitteeElections(Designation $designation): void
    {
        while ($committeeElections = $this->committeeElectionRepository->findAllToNotify($designation)) {
            foreach ($committeeElections as $committeeElection) {
                $memberships = $this->committeeMembershipRepository->findVotingMemberships($committee = $committeeElection->getCommittee());

                foreach ($memberships as $membership) {
                    $this->eventDispatcher->dispatch(new CommitteeElectionCandidacyPeriodIsOverEvent(
                        $membership->getAdherent(),
                        $designation,
                        $committee
                    ));
                }

                $committeeElection->setAdherentNotified(true);

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();
        }
    }

    private function notifyForForElectionResults(\DateTimeInterface $date): void
    {
        $designations = $this->designationRepository->getWithActiveResultsPeriod($date);

        foreach ($designations as $designation) {
            foreach ($this->electionRepository->findAllForDesignation($designation) as $election) {
                $this->notifyVotersOfElection($election, function (Election $election, array $adherents) {
                    return VotingPlatformResultsReadyMessage::create(
                        $election,
                        $adherents,
                        $this->generateResultPageUrl($election)
                    );
                });

                $election->markSentNotification(Designation::NOTIFICATION_RESULT_READY);
            }

            $this->entityManager->flush();
        }
    }

    private function notifyVotersOfElection(Election $election, callable $messageFactory): void
    {
        $offset = 0;

        while ($recipients = $this->getAdherentChunkForElection($election, $offset, 500)) {
            $this->transactionalMailer->sendMessage($messageFactory($election, $recipients));
            $offset += \count($recipients);
        }
    }

    private function getAdherentChunkForElection(Election $election, int $offset = 0, ?int $limit = null): array
    {
        return $this->entityManager->createQueryBuilder()
            ->from(Adherent::class, 'adherent')
            ->select('PARTIAL adherent.{id, firstName, lastName, emailAddress}')
            ->innerJoin(VotersList::class, 'voters_list', Join::WITH, 'voters_list.election = :election')
            ->innerJoin('voters_list.voters', 'voter', Join::WITH, 'voter.adherent = adherent')
            ->where('adherent.status = :enabled AND adherent.adherent = true')
            ->setParameters([
                'election' => $election,
                'enabled' => Adherent::ENABLED,
            ])
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    private function generateResultPageUrl(Election $election): string
    {
        $designation = $election->getDesignation();

        if ($designation->isLocalElectionType()) {
            return $this->urlGenerator->generate('app_renaissance_departmental_election_lists', ['uuid' => $designation->getUuid()->toString()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->urlGenerator->generate('app_voting_platform_results', ['uuid' => $election->getUuid()->toString()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
