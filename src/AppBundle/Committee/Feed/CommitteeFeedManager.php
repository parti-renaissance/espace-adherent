<?php

namespace AppBundle\Committee\Feed;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeMessageNotificationMessage;
use Doctrine\Common\Persistence\ObjectManager;

class CommitteeFeedManager
{
    private $manager;
    private $committeeManager;
    private $mailjet;

    public function __construct(ObjectManager $manager, CommitteeManager $committeeManager, MailjetService $mailjet)
    {
        $this->manager = $manager;
        $this->committeeManager = $committeeManager;
        $this->mailjet = $mailjet;
    }

    public function createEvent(CommitteeEvent $event): CommitteeFeedItem
    {
        $item = CommitteeFeedItem::createEvent(
            $event->getEvent(),
            $event->getAuthor(),
            $event->getCreatedAt()->format(DATE_RFC2822)
        );

        $this->manager->persist($item);
        $this->manager->flush();

        return $item;
    }

    public function createMessage(CommitteeMessage $message): CommitteeFeedItem
    {
        $item = CommitteeFeedItem::createMessage(
            $message->getCommittee(),
            $message->getAuthor(),
            $message->getContent(),
            $message->getCreatedAt()->format(DATE_RFC2822)
        );

        $this->manager->persist($item);
        $this->manager->flush();

        $this->sendMessageToFollowers($item, $message->getCommittee());

        return $item;
    }

    private function sendMessageToFollowers(CommitteeFeedItem $message, Committee $committee)
    {
        $followers = $this->committeeManager->getCommitteeFollowers($committee)->getCommitteesNotificationsSubscribers();

        $this->mailjet->sendMessage(CommitteeMessageNotificationMessage::create($followers->toArray(), $message));
    }
}
