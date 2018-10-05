<?php

namespace AppBundle\Event;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use AppBundle\Mail\Transactional\EventInvitationMail;
use AppBundle\Routing\RemoteUrlGenerator;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class EventInvitationHandler
{
    private $manager;
    private $mailPost;
    private $urlGenerator;

    public function __construct(ObjectManager $manager, MailPostInterface $mailPost, RemoteUrlGenerator $urlGenerator)
    {
        $this->manager = $manager;
        $this->mailPost = $mailPost;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(EventInvitation $invitation, Event $event)
    {
        $invite = EventInvite::create($event, $invitation);

        $url = $this->urlGenerator->generateRemoteUrl('app_event_show', [
            'slug' => $event->getSlug(),
        ]);

        $this->mailPost->address(
            EventInvitationMail::class,
            EventInvitationMail::createRecipientFor($invite),
            EventInvitationMail::createReplyToFrom($invite),
            EventInvitationMail::createTemplateVarsFrom($invite, $event, $url),
            EventInvitationMail::createSubjectFor($invite)
        );

        $this->manager->persist($invite);
        $this->manager->flush();
    }
}
