<?php

namespace App\Invitation;

use App\Entity\Invite;
use App\Mailer\MailerService;
use App\Mailer\Message\MovementInvitationMessage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class InvitationRequestHandler
{
    private $entityManager;
    private $mailer;

    public function __construct(EntityManager $entityManager, MailerService $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    public function handle(Invite $invite, Request $request)
    {
        $invite->setClientIp($request->getClientIp());

        $this->entityManager->persist($invite);
        $this->entityManager->flush();

        $this->mailer->sendMessage(MovementInvitationMessage::createFromInvite($invite));
    }
}
