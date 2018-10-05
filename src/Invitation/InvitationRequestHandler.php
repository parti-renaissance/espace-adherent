<?php

namespace AppBundle\Invitation;

use AppBundle\Entity\Invite;
use AppBundle\Mail\Transactional\InvitationMail;
use AppBundle\Mailer\Message\InvitationMessage;
use Doctrine\ORM\EntityManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\HttpFoundation\Request;

class InvitationRequestHandler
{
    private $entityManager;
    private $mailPost;

    public function __construct(EntityManager $entityManager, MailPostInterface $mailPost)
    {
        $this->entityManager = $entityManager;
        $this->mailPost = $mailPost;
    }

    public function handle(Invite $invite, Request $request)
    {
        $invite->setClientIp($request->getClientIp());

        $this->entityManager->persist($invite);
        $this->entityManager->flush();

        $this->mailPost->address(
            InvitationMail::class,
            InvitationMail::createRecipientFor($invite),
            null,
            InvitationMail::createTemplateVarsFrom($invite),
            InvitationMail::createSubjectFor($invite)
        );
    }
}
