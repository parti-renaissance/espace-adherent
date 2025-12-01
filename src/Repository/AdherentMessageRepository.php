<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageReach;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AdherentMessage\AdherentMessage>
 */
class AdherentMessageRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentMessage::class);
    }

    public function removeAuthorItems(Adherent $author): void
    {
        $queryBuilder = $this->createQueryBuilder('message');

        $this->withAuthor($queryBuilder, $author);

        foreach ($queryBuilder->getQuery()->iterate() as $message) {
            $this->getEntityManager()->remove($message[0]);
        }

        $this->getEntityManager()->flush();
    }

    public function withInstanceScope(QueryBuilder $queryBuilder, string $instanceScope, string $alias = 'message'): self
    {
        $queryBuilder
            ->andWhere("$alias.instanceScope = :instance_scope")
            ->setParameter('instance_scope', $instanceScope)
        ;

        return $this;
    }

    public function withSource(QueryBuilder $queryBuilder, string $source, string $alias = 'message'): self
    {
        $queryBuilder
            ->andWhere("$alias.source = :source")
            ->setParameter('source', $source)
        ;

        return $this;
    }

    public function withAuthor(QueryBuilder $queryBuilder, Adherent $adherent, string $alias = 'message'): self
    {
        $queryBuilder
            ->andWhere("$alias.author = :author")
            ->setParameter('author', $adherent)
        ;

        return $this;
    }

    public function withStatus(QueryBuilder $queryBuilder, string $status, string $alias = 'message'): self
    {
        $queryBuilder
            ->andWhere("$alias.status = :status")
            ->setParameter('status', $status)
        ;

        return $this;
    }

    public function countReachAll(int $messageId): array
    {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin(AdherentMessageReach::class, 'r', 'WITH', 'r.message = m')
            ->where('m = :message')
            ->setParameter('message', $messageId)
            ->select([
                'COUNT(DISTINCT r.adherent) AS total',
                'COUNT(DISTINCT IF(r.source = :email OR r.source LIKE :push, r.adherent, NULL)) AS email_push',
                'COUNT(DISTINCT IF(r.source = :email, r.adherent, NULL)) AS email',
                'COUNT(DISTINCT IF(r.source LIKE :push, r.adherent, NULL)) AS push',
                'COUNT(DISTINCT IF(r.source = :push_web, r.adherent, NULL)) AS push_web',
                'COUNT(DISTINCT IF(r.source = :push_ios, r.adherent, NULL)) AS push_ios',
                'COUNT(DISTINCT IF(r.source = :push_android, r.adherent, NULL)) AS push_android',
            ])
            ->setParameter('email', 'email')
            ->setParameter('push', 'push%')
            ->setParameter('push_web', 'push:web')
            ->setParameter('push_ios', 'push:ios')
            ->setParameter('push_android', 'push:android')
        ;

        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY) ?? [
            'total' => 0,
            'email' => 0,
            'push' => 0,
            'email_push' => 0,
            'push_web' => 0,
            'push_ios' => 0,
            'push_android' => 0,
        ];
    }
}
