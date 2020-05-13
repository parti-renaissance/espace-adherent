<?php

namespace App\Event;

use App\Entity\Event;
use App\Entity\EventInvite;
use App\Mailer\MailerService;
use App\Mailer\Message\EventInvitationMessage;
use App\Routing\RemoteUrlGenerator;
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
        ]);

        $this->mailer->sendMessage(EventInvitationMessage::createFromInvite($invite, $event, $url));

        $this->manager->persist($invite);
        $this->manager->flush();
    }
}
