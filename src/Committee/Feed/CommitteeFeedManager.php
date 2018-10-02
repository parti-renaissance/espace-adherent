<?php

namespace AppBundle\Committee\Feed;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mail\Campaign\CommitteeMessageNotificationMail;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class CommitteeFeedManager
{
    private $manager;
    private $committeeManager;
    private $mailPost;

    public function __construct(ObjectManager $manager, CommitteeManager $committeeManager, MailPostInterface $mailPost)
    {
        $this->manager = $manager;
        $this->committeeManager = $committeeManager;
        $this->mailPost = $mailPost;
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

        if ($message->isSendNotification()) {
            $this->sendMessageToFollowers($item, $message->getCommittee(), $message->getSubject());
        }

        return $item;
    }

    private function sendMessageToFollowers(CommitteeFeedItem $message, Committee $committee, string $subject): void
    {
        $this->mailPost->address(
            CommitteeMessageNotificationMail::class,
            CommitteeMessageNotificationMail::createRecipientsFrom(
                $this->committeeManager->getOptinCommitteeFollowers($committee)->toArray()
            ),
            CommitteeMessageNotificationMail::createRecipientFromAdherent($message->getAuthor()),
            CommitteeMessageNotificationMail::createTemplateVars($message),
            CommitteeMessageNotificationMail::createSubject($subject),
            CommitteeMessageNotificationMail::createSender($message->getAuthor())
        );
    }
}
