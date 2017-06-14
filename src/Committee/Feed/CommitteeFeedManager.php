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
            true,
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
            $message->isPublished(),
            $message->getCreatedAt()->format(DATE_RFC2822)
        );

        $this->manager->persist($item);
        $this->manager->flush();

        $this->sendMessageToFollowers($item, $message->getCommittee());

        return $item;
    }

    private function sendMessageToFollowers(CommitteeFeedItem $message, Committee $committee): void
    {
        $chunks = array_chunk(
            $this->committeeManager->getOptinCommitteeFollowers($committee)->toArray(),
            MailjetService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->mailjet->sendMessage(CommitteeMessageNotificationMessage::create($chunk, $message));
        }
    }
}
