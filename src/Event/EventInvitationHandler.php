<?php

namespace AppBundle\Event;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\EventInvitationMessage;
use AppBundle\Routing\RemoteUrlGenerator;
use Doctrine\Common\Persistence\ObjectManager;

class EventInvitationHandler
{
    private $manager;
    private $mailer;
    private $urlGenerator;

    public function __construct(ObjectManager $manager, MailerService $mailer, RemoteUrlGenerator $urlGenerator)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(EventInvitation $invitation, Event $event)
    {
        $invite = EventInvite::create($event, $invitation);

        $url = $this->urlGenerator->generateRemoteUrl('app_event_show', [
            'slug' => $event->getSlug(),
            'uuid' => $event->getUuid()->toString(),
        ]);

        $this->mailer->sendMessage(EventInvitationMessage::createFromInvite($invite, $event, $url));

        $this->manager->persist($invite);
        $this->manager->flush();
    }
}
