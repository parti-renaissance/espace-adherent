<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ReferentManagedUsersMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentManagedUsersMessageRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReferentManagedUsersMessage::class);
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
