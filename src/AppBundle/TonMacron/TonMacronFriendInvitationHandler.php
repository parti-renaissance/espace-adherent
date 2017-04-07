<?php

namespace AppBundle\TonMacron;

use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\TonMacronFriendMessage;
use Doctrine\Common\Persistence\ObjectManager;

class TonMacronFriendInvitationHandler
{
    private $builder;
    private $mailjet;
    private $manager;

    public function __construct(
        ObjectManager $manager,
        TonMacronMessageBodyBuilder $builder,
        MailjetService $mailjet
    ) {
        $this->manager = $manager;
        $this->builder = $builder;
        $this->mailjet = $mailjet;
    }

    public function handle(TonMacronFriendInvitation $invitation): void
    {
        $invitation->setMailBody($body = $this->builder->buildMessageBody($invitation));

        $this->manager->flush();

        $this->mailjet->sendMessage(TonMacronFriendMessage::createFromInvitation($invitation, $body));
    }
}
