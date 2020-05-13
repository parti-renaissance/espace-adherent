<?php

namespace App\Committee\EventListener;

use App\Committee\CommitteeManager;
use App\Committee\Event\FollowCommitteeEvent;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeNewFollowerMessage;
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
