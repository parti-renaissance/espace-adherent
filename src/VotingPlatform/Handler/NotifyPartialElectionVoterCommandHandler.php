<?php

namespace App\VotingPlatform\Handler;

use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Mailer\MailerService;
use App\Mailer\Message\VotingPlatformPartialElectionIsOpenMessage;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use App\VotingPlatform\Command\NotifyPartialElectionVoterCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotifyPartialElectionVoterCommandHandler implements MessageHandlerInterface
{
    private $mailer;
    private $committeeRepository;
    private $committeeMembershipRepository;

    public function __construct(
        MailerService $transactionalMailer,
        CommitteeRepository $committeeRepository,
        CommitteeMembershipRepository $committeeMembershipRepository
    ) {
        $this->mailer = $transactionalMailer;
        $this->committeeRepository = $committeeRepository;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    public function __invoke(NotifyPartialElectionVoterCommand $command): void
    {
        $committee = $this->committeeRepository->findOneBy(['id' => $command->getCommitteeId(), 'status' => Committee::APPROVED]);

        if (!$committee) {
            return;
        }

        $designation = $committee->getCurrentDesignation();

        if (!$designation) {
            return;
        }

        if ($designation->isCommitteeType()) {
            $memberships = [];

            if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
                $memberships = $this->committeeMembershipRepository->findVotingMemberships($committee);
            } elseif (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
                $memberships = $this->committeeMembershipRepository->findVotingForSupervisorMemberships($committee, $designation, false);
            }

            if ($memberships) {
                $this->mailer->sendMessage(VotingPlatformPartialElectionIsOpenMessage::create(
                    $designation,
                    $designation->getDescription(),
                    $committee->getName(),
                    array_map(function (CommitteeMembership $membership) {return $membership->getAdherent(); }, $memberships)
                ));
            }
        }
    }
}
