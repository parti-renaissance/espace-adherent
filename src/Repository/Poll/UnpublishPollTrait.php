<?php

declare(strict_types=1);

namespace App\Repository\Poll;

use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\Poll;

trait UnpublishPollTrait
{
    public function unpublishExceptOf(Poll $poll): void
    {
        $qb = $this->createQueryBuilder('poll')
            ->update()
            ->set('poll.published', ':false')
            ->where('poll != :poll')
            ->setParameters([
                'poll' => $poll,
                'false' => 0,
            ])
        ;

        if ($poll instanceof LocalPoll) {
            $qb
                ->andWhere('poll.zone = :zone')
                ->setParameter('zone', $poll->getZone())
            ;
        }

        $qb->getQuery()->execute();
    }
}
