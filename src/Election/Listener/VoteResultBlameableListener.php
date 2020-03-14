<?php

namespace AppBundle\Election\Listener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Election\BaseVoteResult;
use AppBundle\Entity\Election\ListTotalResult;
use AppBundle\Entity\Election\MinistryListTotalResult;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
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

        $entityManager = $args->getEntityManager();
        $uow = $entityManager->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $listTotalResult) {
            if (!\in_array(\get_class($listTotalResult), [ListTotalResult::class, MinistryListTotalResult::class], true)) {
                continue;
            }

            if ($this->updateBlameableData($listTotalResult, $user, $uow, $entityManager)) {
                return;
            }
        }

        foreach (array_merge($uow->getScheduledEntityDeletions(), $uow->getScheduledEntityInsertions()) as $listTotalResult) {
            if (!$listTotalResult instanceof MinistryListTotalResult) {
                continue;
            }

            if ($this->updateBlameableData($listTotalResult, $user, $uow, $entityManager)) {
                return;
            }
        }
    }

    private function updateBlameableData(
        $listTotalResult,
        Adherent $user,
        UnitOfWork $uow,
        EntityManagerInterface $entityManager
    ): bool {
        if ($voteResult = $this->getVoteResult($listTotalResult)) {
            $voteResult->setUpdatedBy($user);
            $voteResult->setUpdatedAt(new \DateTime());

            $uow->recomputeSingleEntityChangeSet(
                $entityManager->getClassMetadata(\get_class($voteResult)),
                $voteResult
            );

            return true;
        }

        return false;
    }

    private function getVoteResult($listTotalResult): ?BaseVoteResult
    {
        if ($listTotalResult instanceof ListTotalResult) {
            return $listTotalResult->getVoteResult();
        }

        if ($listTotalResult instanceof MinistryListTotalResult) {
            return $listTotalResult->getMinistryVoteResult();
        }

        return null;
    }
}
