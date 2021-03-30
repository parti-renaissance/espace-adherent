<?php

namespace App\Committee\Feed;

use App\Entity\CommitteeFeedItem;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;

class CommitteeFeedManager
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function createEvent(CommitteeEvent $event): CommitteeFeedItem
    {
        $item = CommitteeFeedItem::createEvent(
            $event->getEvent(),
            $event->getAuthor(),
            true,
            $event->getCreatedAt()->format(\DATE_RFC2822)
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
            $message->isPublished(),
            $message->getCreatedAt()->format(\DATE_RFC2822)
        );

        $this->manager->persist($item);
        $this->manager->flush();

        return $item;
    }
}
