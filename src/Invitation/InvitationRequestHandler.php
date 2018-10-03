<?php

namespace AppBundle\Invitation;

use AppBundle\Entity\Invite;
use AppBundle\Mail\Transactional\InvitationMail;
use Doctrine\ORM\EntityManagerInterface;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\HttpFoundation\Request;

class InvitationRequestHandler
{
    private $entityManager;
    private $mailPost;

    public function __construct(EntityManagerInterface $entityManager, MailPostInterface $mailPost)
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
            InvitationMail::createRecipient($invite),
            null,
            InvitationMail::createTemplateVars($invite),
            InvitationMail::createSubject($invite)
        );
    }
}
