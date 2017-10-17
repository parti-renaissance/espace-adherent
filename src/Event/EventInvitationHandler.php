<?php

namespace AppBundle\Event;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\EventInvitationMessage;
use AppBundle\Routing\RemoteUrlGenerator;
use Doctrine\Common\Persistence\ObjectManager;

class EventInvitationHandler
{
    private $manager;
    private $mailjet;
    private $urlGenerator;

    public function __construct(ObjectManager $manager, MailjetService $mailjet, RemoteUrlGenerator $urlGenerator)
    {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(EventInvitation $invitation, Event $event)
    {
        $invite = EventInvite::create($event, $invitation);

        $url = $this->urlGenerator->generateRemoteUrl('app_event_show', [
            'slug' => $event->getSlug(),
        ]);

        $this->mailjet->sendMessage(EventInvitationMessage::createFromInvite($invite, $event, $url));

        $this->manager->persist($invite);
        $this->manager->flush();
    }
}
