<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AdherentMessageRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractAdherentMessage::class);
    }

    public function findAllByAuthor(Adherent $adherent, string $status = null, string $type = null): array
    {
        $queryBuilder = $this->createQueryBuilder('message')
            ->where('message.author = :author')
            ->setParameter('author', $adherent)
        ;

        if ($status) {
            $queryBuilder
                ->andWhere('message.status = :status')
                ->setParameter('status', $status)
            ;
        }

        if ($type) {
            $queryBuilder
                ->andWhere('message INSTANCE OF :type')
                ->setParameter('type', $type)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
