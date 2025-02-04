<?php

namespace App\Event;

use App\Entity\Event\Event;
use App\Entity\Event\EventInvite;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceEventInvitationMessage;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventInvitationHandler
{
    private $manager;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        ObjectManager $manager,
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
    ) {
        $this->manager = $manager;
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(EventInvitation $invitation, Event $event)
    {
        $invite = EventInvite::create($event, $invitation);

        $url = $this->urlGenerator->generate('app_renaissance_event_show', [
            'slug' => $event->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendMessage(RenaissanceEventInvitationMessage::createFromInvite($invite, $event, $url));

        $this->manager->persist($invite);
        $this->manager->flush();
    }
}
