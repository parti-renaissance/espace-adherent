<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\EventInvite;
use AppBundle\Event\EventInvitation;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CitizenInitiativeInvitationMessage;
use AppBundle\Routing\RemoteUrlGenerator;
use Doctrine\Common\Persistence\ObjectManager;

class CitizenInitiativeInvitationHandler
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

    public function handle(EventInvitation $invitation, CitizenInitiative $initiative)
    {
        $invite = EventInvite::create($initiative, $invitation);

        $url = $this->urlGenerator->generateRemoteUrl('app_citizen_initiative_show', [
            'slug' => $initiative->getSlug(),
        ]);

        $this->mailjet->sendMessage(CitizenInitiativeInvitationMessage::createFromInvite($invite, $initiative, $url));

        $this->manager->persist($invite);
        $this->manager->flush();
    }
}
