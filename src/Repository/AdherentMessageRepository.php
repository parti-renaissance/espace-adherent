<?php

namespace AppBundle\Repository;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\Committee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

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

    public function findAllCommitteeMessage(
        Adherent $adherent,
        Committee $committee = null,
        string $status = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('message')
            ->where('message.author = :author')
            ->andWhere('message INSTANCE OF :type')
            ->setParameter('author', $adherent)
            ->setParameter('type', AdherentMessageTypeEnum::COMMITTEE)
        ;

        if ($status) {
            $queryBuilder
                ->andWhere('message.status = :status')
                ->setParameter('status', $status)
            ;
        }

        if ($committee) {
            $queryBuilder
                ->innerJoin(CommitteeFilter::class, 'filter', Join::WITH, 'message.filter = filter')
                ->andWhere('filter.committee = :committee')
                ->setParameter('committee', $committee)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
