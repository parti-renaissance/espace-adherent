<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class AdherentMessageRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

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
}
