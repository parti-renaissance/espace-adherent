<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ReferentManagedUsersMessage;
use Doctrine\ORM\EntityRepository;

class ReferentManagedUsersMessageRepository extends EntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(string $uuid): ?ReferentManagedUsersMessage
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function incrementOffset(ReferentManagedUsersMessage $message, int $increment, bool $flush = true): void
    {
        $message->incrementOffset($increment);

        if ($flush) {
            $this->_em->flush();
        }
    }
}
