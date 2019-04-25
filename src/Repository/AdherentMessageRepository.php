<?php

namespace AppBundle\Repository;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator as ApiPaginator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\CitizenProjectFilter;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class AdherentMessageRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractAdherentMessage::class);
    }

    /**
     * @return AdherentMessageInterface[]
     */
    public function findAllByAuthor(
        Adherent $adherent,
        string $status = null,
        string $type = null,
        int $page = 1
    ): PaginatorInterface {
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

        return $this->configurePaginator($queryBuilder, $page);
    }

    /**
     * @return CommitteeAdherentMessage[]
     */
    public function findAllCommitteeMessage(
        Adherent $adherent,
        Committee $committee = null,
        string $status = null,
        int $page = 1
    ): PaginatorInterface {
        $queryBuilder = $this->createQueryBuilder('message');

        $this
            ->withMessageType($queryBuilder, AdherentMessageTypeEnum::COMMITTEE)
            ->withAuthor($queryBuilder, $adherent)
        ;

        if ($status) {
            $this->withStatus($queryBuilder, $status);
        }

        if ($committee) {
            $queryBuilder
                ->innerJoin(CommitteeFilter::class, 'filter', Join::WITH, 'message.filter = filter')
                ->andWhere('filter.committee = :committee')
                ->setParameter('committee', $committee)
            ;
        }

        return $this->configurePaginator($queryBuilder, $page);
    }

    /**
     * @return CitizenProjectAdherentMessage[]
     */
    public function findAllCitizenProjectMessage(
        Adherent $adherent,
        CitizenProject $citizenProject,
        string $status = null,
        int $page = 1
    ): PaginatorInterface {
        $queryBuilder = $this->createQueryBuilder('message');

        $this
            ->withMessageType($queryBuilder, AdherentMessageTypeEnum::CITIZEN_PROJECT)
            ->withAuthor($queryBuilder, $adherent)
        ;

        if ($status) {
            $this->withStatus($queryBuilder, $status);
        }

        $queryBuilder
            ->innerJoin(CitizenProjectFilter::class, 'filter', Join::WITH, 'message.filter = filter')
            ->andWhere('filter.citizenProject = :citizen_project')
            ->setParameter('citizen_project', $citizenProject)
        ;

        return $this->configurePaginator($queryBuilder, $page);
    }

    private function withMessageType(QueryBuilder $queryBuilder, string $messageType, string $alias = 'message'): self
    {
        if (!AdherentMessageTypeEnum::isValid($messageType)) {
            throw new \InvalidArgumentException('Message type is invalid');
        }

        $queryBuilder
            ->andWhere("$alias INSTANCE OF :type")
            ->setParameter('type', $messageType)
        ;

        return $this;
    }

    private function withAuthor(QueryBuilder $queryBuilder, Adherent $adherent, string $alias = 'message'): self
    {
        $queryBuilder
            ->andWhere("$alias.author = :author")
            ->setParameter('author', $adherent)
        ;

        return $this;
    }

    private function withStatus(QueryBuilder $queryBuilder, string $status, string $alias = 'message'): self
    {
        $queryBuilder
            ->andWhere("$alias.status = :status")
            ->setParameter('status', $status)
        ;

        return $this;
    }

    private function configurePaginator(QueryBuilder $queryBuilder, int $page, int $limit = 30): PaginatorInterface
    {
        if ($page < 1) {
            $page = 1;
        }

        return new ApiPaginator(new DoctrinePaginator($queryBuilder
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getQuery()
        ));
    }
}
