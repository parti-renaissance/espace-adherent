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

    public function handle(EventInvitation $invitation, ?string $ip, Event $event)
    {
        foreach ($invitation->guests as $guest) {
            $invite = EventInvite::create($invitation->firstName, $invitation->lastName, $guest, $ip, $event);
            $url = $this->urlGenerator->generateRemoteUrl('app_committee_show_event', [
                'slug' => $event->getSlug(),
                'uuid' => $event->getUuid()->toString(),
            ]);

            $this->manager->persist($invite);
            $this->mailjet->sendMessage(EventInvitationMessage::createFromInvite($invite, $event->getName(), $url));
        }

        $this->manager->flush();
    }
}
