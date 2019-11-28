<?php

namespace AppBundle\Committee\EventListener;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\Event\FollowCommitteeEvent;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CommitteeNewFollowerMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendEmailForNewFollowerListener implements EventSubscriberInterface
{
    private $manager;
    private $urlGenerator;
    private $mailer;

    public function __construct(CommitteeManager $manager, UrlGeneratorInterface $urlGenerator, MailerService $mailer)
    {
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::COMMITTEE_NEW_FOLLOWER => 'sendEmail',
        ];
    }

    public function sendEmail(FollowCommitteeEvent $event): void
    {
        $committee = $event->getCommittee();

        if (!$hosts = $this->manager->getCommitteeHosts($committee)->toArray()) {
            return;
        }

        $this->mailer->sendMessage(CommitteeNewFollowerMessage::create(
            $committee,
            $hosts,
            $event->getAdherent(),
            $this->urlGenerator->generate('app_committee_manager_list_members', [
                'slug' => $committee->getSlug(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}
