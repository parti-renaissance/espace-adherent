<?php

namespace AppBundle\Election\Listener;

use AppBundle\Entity\Election\ListTotalResult;
use AppBundle\Entity\Election\VotePlaceResult;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Security;

class VoteResultBlameableListener implements EventSubscriber
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush => 'onFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        if (!$user = $this->security->getUser()) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $listTotalResult) {
            if (!$listTotalResult instanceof ListTotalResult) {
                continue;
            }

            if ($voteResult = $listTotalResult->getVoteResult()) {
                $voteResult->setUpdatedBy($user);
                $voteResult->setUpdatedAt(new \DateTime());

                $uow->recomputeSingleEntityChangeSet(
                    $args->getEntityManager()->getClassMetadata(VotePlaceResult::class),
                    $voteResult
                );

                return;
            }
        }
    }
}
