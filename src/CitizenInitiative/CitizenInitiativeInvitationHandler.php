<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\EventInvite;
use AppBundle\Event\EventInvitation;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenInitiativeInvitationMessage;
use AppBundle\Routing\RemoteUrlGenerator;
use Doctrine\Common\Persistence\ObjectManager;

class CitizenInitiativeInvitationHandler
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

    public function handle(EventInvitation $invitation, CitizenInitiative $initiative)
    {
        $invite = EventInvite::create($initiative, $invitation);

        $url = $this->urlGenerator->generateRemoteUrl('app_citizen_initiative_show', [
            'slug' => $initiative->getSlug(),
            'uuid' => $initiative->getUuid()->toString(),
        ]);

        $this->mailer->sendMessage(CitizenInitiativeInvitationMessage::createFromInvite($invite, $initiative, $url));

        $this->manager->persist($invite);
        $this->manager->flush();
    }
}
