<?php

namespace App\Poll;

use App\Entity\Poll\Poll;
use Doctrine\ORM\EntityManagerInterface;

class PollManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function publish(Poll $poll): void
    {
        $poll->setPublished(true);
        $this->entityManager->getRepository(\get_class($poll))->unpublishExceptOf($poll);

        $this->entityManager->flush();
    }

    public function unpublish(Poll $poll): void
    {
        $poll->setPublished(false);

        $this->entityManager->flush();
    }
}
