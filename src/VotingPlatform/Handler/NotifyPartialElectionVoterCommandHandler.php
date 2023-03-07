<?php

namespace App\VotingPlatform\Handler;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Mailer\MailerService;
use App\Mailer\Message\VotingPlatformPartialElectionIsOpenMessage;
use App\Producer\MailerProducerInterface;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use App\VotingPlatform\Command\NotifyPartialElectionVoterCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotifyPartialElectionVoterCommandHandler implements MessageHandlerInterface
{
    private $mailer;
    private $committeeRepository;
    private $committeeMembershipRepository;
    private $urlGenerator;
    private $mailerProducer;

    public function __construct(
        MailerService $transactionalMailer,
        CommitteeRepository $committeeRepository,
        CommitteeMembershipRepository $committeeMembershipRepository,
        UrlGeneratorInterface $urlGenerator,
        MailerProducerInterface $mailerProducer
    ) {
        $this->mailer = $transactionalMailer;
        $this->committeeRepository = $committeeRepository;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->urlGenerator = $urlGenerator;
        $this->mailerProducer = $mailerProducer;
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

        $this->mailerProducer->reconnect();

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
