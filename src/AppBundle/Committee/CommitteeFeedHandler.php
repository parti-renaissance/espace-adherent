<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedMessage;
use Doctrine\Common\Persistence\ObjectManager;

class CommitteeFeedHandler
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function createMessage(Feed\CommitteeMessage $message, Committee $committee, Adherent $adherent): CommitteeFeedMessage
    {
        $message = CommitteeFeedMessage::create($message->content, $committee, $adherent);

        $this->manager->persist($message);
        $this->manager->flush();

        return $message;
    }
}
