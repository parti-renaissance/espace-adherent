<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeFeedNotificationMessage;
use Doctrine\Common\Persistence\ObjectManager;

class CommitteeFeedHandler
{
    private $manager;
    private $committeeManager;
    private $mailJet;

    public function __construct(ObjectManager $manager, CommitteeManager $committeeManager, MailjetService $mailjet)
    {
        $this->manager = $manager;
        $this->committeeManager = $committeeManager;
        $this->mailJet = $mailjet;
    }

    public function createMessage(Feed\CommitteeMessage $message, Committee $committee, Adherent $adherent): CommitteeFeedItem
    {
        $message = CommitteeFeedItem::createMessage($committee, $adherent, $message->content);

        $this->sendMessageToFollowers($message, $committee);

        $this->manager->persist($message);
        $this->manager->flush();

        return $message;
    }

    private function sendMessageToFollowers(CommitteeFeedItem $message, Committee $committee)
    {
        $followers = $this->committeeManager->getCommitteeFollowers($committee)->getCommitteesNotificationsSubscribers();

        $this->mailJet->sendMessage(CommitteeFeedNotificationMessage::create($followers->toArray(), $message));
    }
}
