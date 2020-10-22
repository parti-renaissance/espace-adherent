<?php

namespace App\ThematicCommunity\Handler;

use App\Entity\Adherent;
use App\Entity\ThematicCommunity\ContactMembership;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Mailer\MailerService;
use App\Mailer\Message\ThematicCommunity\ThematicCommunityAlreadyContactMemberMessage;
use App\Mailer\Message\ThematicCommunity\ThematicCommunityContactJoinAsAdherentMessage;
use App\Mailer\Message\ThematicCommunity\ThematicCommunityJoinedPendingMessage;
use App\Repository\AdherentRepository;
use App\Repository\ThematicCommunity\ThematicCommunityMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;

class ThematicCommunityMembershipHandler
{
    /** @var MailerService */
    private $transactionalMailer;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ThematicCommunityMembershipRepository */
    private $communityMembershipRepository;

    /** @var AdherentRepository */
    private $adherentRepository;

    public function __construct(MailerService $transactionalMailer, EntityManagerInterface $entityManager)
    {
        $this->transactionalMailer = $transactionalMailer;
        $this->entityManager = $entityManager;
        $this->communityMembershipRepository = $entityManager->getRepository(ThematicCommunityMembership::class);
        $this->adherentRepository = $entityManager->getRepository(Adherent::class);
    }

    public function join(ThematicCommunityMembership $membership): bool
    {
        if (!$membership->getCommunity()->isEnabled()) {
            throw new \LogicException('You cannot to join a disabled thematic community');
        }

        if ($membership instanceof ContactMembership) {
            $alreadyMember = $this->communityMembershipRepository->isEmailContactAlreadyRegisteredOnCommunity($membership->getCommunity(), $membership->getEmail());

            if ($alreadyMember) {
                $this->transactionalMailer->sendMessage(ThematicCommunityAlreadyContactMemberMessage::create($membership));

                return false;
            }

            $isAdherentEmail = $this->adherentRepository->findOneBy(['emailAddress' => $membership->getEmail()]);
            if ($isAdherentEmail) {
                $this->save($membership);
                $this->transactionalMailer->sendMessage(ThematicCommunityContactJoinAsAdherentMessage::create($membership));

                return true;
            }
        }

        $this->save($membership);
        $this->sendConfirmEmail($membership);

        return true;
    }

    public function sendConfirmEmail(ThematicCommunityMembership $membership)
    {
        $this->transactionalMailer->sendMessage(ThematicCommunityJoinedPendingMessage::create($membership));
    }

    public function editMembership(ThematicCommunityMembership $membership)
    {
        $this->save($membership);
    }

    public function unsubscribe(ThematicCommunityMembership $membership): void
    {
        $this->entityManager->remove($membership);
        $this->entityManager->flush();
    }

    public function confirmMembership(ThematicCommunityMembership $membership): void
    {
        $membership->setStatus(ThematicCommunityMembership::STATUS_VERIFIED);
        $this->save($membership);
    }

    protected function save(ThematicCommunityMembership $membership): void
    {
        $this->entityManager->persist($membership);
        $this->entityManager->flush();
    }
}
