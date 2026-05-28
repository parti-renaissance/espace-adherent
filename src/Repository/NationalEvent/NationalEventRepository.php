<?php

declare(strict_types=1);

namespace App\Repository\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NationalEventRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NationalEvent::class);
    }

    /**
     * Returns the event currently happening (now between startDate and endDate),
     * or as a fallback the next one about to start. Used both by the public landing
     * page and the admin step 1 pre-selection. Sorting by startDate ASC over
     * endDate >= now naturally yields ongoing events first, then upcoming.
     */
    public function findCurrentOrNext(?array $allowedTypes = null, ?array $forbiddenTypes = null): ?NationalEvent
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.endDate >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.startDate', 'ASC')
            ->setMaxResults(1)
        ;

        if (null !== $allowedTypes && \count($allowedTypes) > 0) {
            $qb->andWhere('e.type IN (:allowed_types)')->setParameter('allowed_types', $allowedTypes);
        }

        if (null !== $forbiddenTypes && \count($forbiddenTypes) > 0) {
            $qb->andWhere('e.type NOT IN (:forbidden_types)')->setParameter('forbidden_types', $forbiddenTypes);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return NationalEvent[]
     */
    public function findOneActiveForAlert(): array
    {
        return $this->createQueryBuilder('event')
            ->where('event.endDate > :now')
            ->andWhere('event.alertEnabled = 1')
            ->setParameter('now', new \DateTime())
            ->orderBy('event.startDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySlug(string $part): ?NationalEvent
    {
        return $this->findOneBy(['slug' => $part]);
    }

    public function findAllSince(\DateTime $since, array $types = []): array
    {
        $qb = $this->createQueryBuilder('event')
            ->where('event.startDate >= :start_date')
            ->setParameter('start_date', $since)
            ->orderBy('event.startDate', 'DESC')
        ;

        if (\count($types) > 0) {
            $qb
                ->andWhere('event.type IN (:types)')
                ->setParameter('types', $types)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
