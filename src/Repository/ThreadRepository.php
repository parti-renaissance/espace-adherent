<?php

namespace App\Repository;

use App\Entity\IdeasWorkshop\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ThreadRepository extends ServiceEntityRepository
{
    use AuthorTrait;
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function findOneByUuid(string $uuid, bool $disabledEntity = false): Thread
    {
        if ($disabledEntity && $this->_em->getFilters()->isEnabled('enabled')) {
            $this->_em->getFilters()->disable('enabled');
        }

        static::validUuid($uuid);

        return $this->findOneBy(['uuid' => $uuid]);
    }
}
