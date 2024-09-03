<?php

namespace App\Event;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventInvite;
use App\Mailer\MailerService;
use App\Mailer\Message\EventInvitationMessage;
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

    public function handle(EventInvitation $invitation, BaseEvent $event)
    {
        $invite = EventInvite::create($event, $invitation);

        $url = $this->urlGenerator->generate($event->isRenaissanceEvent() ? 'app_renaissance_event_show' : 'app_committee_event_show', [
            'slug' => $event->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendMessage(
            $event->isRenaissanceEvent()
            ? RenaissanceEventInvitationMessage::createFromInvite($invite, $event, $url)
            : EventInvitationMessage::createFromInvite($invite, $event, $url)
        );

        $this->manager->persist($invite);
        $this->manager->flush();
    }
}
