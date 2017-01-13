<?php

namespace AppBundle\Invitation;

use AppBundle\Entity\Invite;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\InvitationMessage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class InvitationRequestHandler
{
    private $entityManager;
    private $mailjet;

    public function __construct(EntityManager $entityManager, MailjetService $mailjet)
    {
        $this->entityManager = $entityManager;
        $this->mailjet = $mailjet;
    }

    public function handle(Invite $invite, Request $request)
    {
        $invite->setClientIp($request->getClientIp());

        $this->entityManager->persist($invite);
        $this->entityManager->flush();

        $this->mailjet->sendMessage(InvitationMessage::createFromInvite($invite));
    }
}
