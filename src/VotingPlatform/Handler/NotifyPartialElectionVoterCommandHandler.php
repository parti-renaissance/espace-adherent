<?php

declare(strict_types=1);

namespace App\VotingPlatform\Handler;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Mailer\MailerService;
use App\Mailer\Message\VotingPlatformPartialElectionIsOpenMessage;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use App\VotingPlatform\Command\NotifyPartialElectionVoterCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class NotifyPartialElectionVoterCommandHandler
{
    private $mailer;
    private $committeeRepository;
    private $committeeMembershipRepository;
    private $urlGenerator;

    public function __construct(
        MailerService $transactionalMailer,
        CommitteeRepository $committeeRepository,
        CommitteeMembershipRepository $committeeMembershipRepository,
        UrlGeneratorInterface $urlGenerator,
    ) {
        $this->mailer = $transactionalMailer;
        $this->committeeRepository = $committeeRepository;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->urlGenerator = $urlGenerator;
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

        if ($designation->isCommitteeTypes()) {
            $memberships = $this->committeeMembershipRepository->findVotingForElectionMemberships($committee, $designation, false);

            if ($memberships) {
                $this->mailer->sendMessage(VotingPlatformPartialElectionIsOpenMessage::create(
                    $designation,
                    $designation->getDescription(),
                    $this->getAdherents($memberships, $designation),
                    $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                ));
            }
        }
    }

    private function getAdherents(array $memberships, Designation $designation): array
    {
        $adherents = array_map(function (CommitteeMembership $membership) { return $membership->getAdherent(); }, $memberships);

        if ($pools = $designation->getPools()) {
            return array_filter($adherents, function (Adherent $adherent) use ($pools) {
                return \in_array($adherent->getGender(), $pools, true);
            });
        }

        return $adherents;
    }
}
