<?php

namespace App\Repository;

use App\Entity\ManagedUsersMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class ManagedUsersMessageRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function findOneByUuid(string $uuid): ?ManagedUsersMessage
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function incrementOffset(ManagedUsersMessage $message, int $increment, bool $flush = true): void
    {
        $message->incrementOffset($increment);

        if ($flush) {
            $this->_em->flush();
        }
    }
}
