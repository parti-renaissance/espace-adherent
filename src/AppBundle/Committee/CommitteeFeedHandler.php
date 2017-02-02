<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use Doctrine\Common\Persistence\ObjectManager;

class CommitteeFeedHandler
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function createMessage(Feed\CommitteeMessage $message, Committee $committee, Adherent $adherent): CommitteeFeedItem
    {
        $message = CommitteeFeedItem::createMessage($committee, $adherent, $message->content);

        $this->manager->persist($message);
        $this->manager->flush();

        return $message;
    }
}
