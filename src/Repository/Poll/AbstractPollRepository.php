<?php

namespace App\Repository\Poll;

use App\Entity\Poll\Poll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractPollRepository extends ServiceEntityRepository
{
    public function unpublishExceptOf(Poll $poll): void
    {
        $this->createQueryBuilder('poll')
            ->update()
            ->set('poll.published', ':false')
            ->where('poll != :poll')
            ->setParameters([
                'poll' => $poll,
                'false' => 0,
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
