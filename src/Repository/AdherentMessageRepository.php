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
     * @return AdherentMessageInterface[]|PaginatorInterface
     */
    public function findAllByAuthor(
        Adherent $adherent,
        string $type,
        string $status = null,
        int $page = 1
    ): PaginatorInterface {
        $queryBuilder = $this->createQueryBuilder('message');

        $this
            ->withAuthor($queryBuilder, $adherent)
            ->withMessageType($queryBuilder, $type)
            ->orderByDate($queryBuilder)
        ;

        if ($status) {
            $queryBuilder
                ->andWhere('message.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return $this->configurePaginator($queryBuilder, $page);
    }

    /**
     * @return CommitteeAdherentMessage[]|PaginatorInterface
     */
    public function findAllCommitteeMessage(
        Adherent $adherent,
        Committee $committee,
        string $status = null,
        int $page = 1
    ): PaginatorInterface {
        $queryBuilder = $this->createQueryBuilder('message');

        $this
            ->withMessageType($queryBuilder, AdherentMessageTypeEnum::COMMITTEE)
            ->withAuthor($queryBuilder, $adherent)
            ->withCommittee($queryBuilder, $committee)
            ->orderByDate($queryBuilder)
        ;

        if ($status) {
            $this->withStatus($queryBuilder, $status);
        }

        return $this->configurePaginator($queryBuilder, $page);
    }

    /**
     * @return CitizenProjectAdherentMessage[]|PaginatorInterface
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
            ->withCitizenProject($queryBuilder, $citizenProject)
            ->orderByDate($queryBuilder)
        ;

        if ($status) {
            $this->withStatus($queryBuilder, $status);
        }

        return $this->configurePaginator($queryBuilder, $page);
    }

    public function countTotalMessage(Adherent $adherent, string $type): int
    {
        $queryBuilder = $this
            ->createQueryBuilder('message')
            ->select('COUNT(message.id)')
        ;

        $this
            ->withAuthor($queryBuilder, $adherent)
            ->withMessageType($queryBuilder, $type)
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countTotalCitizenProjectMessage(Adherent $adherent, CitizenProject $citizenProject): int
    {
        $queryBuilder = $this
            ->createQueryBuilder('message')
            ->select('COUNT(message.id)')
        ;

        $this
            ->withMessageType($queryBuilder, AdherentMessageTypeEnum::CITIZEN_PROJECT)
            ->withAuthor($queryBuilder, $adherent)
            ->withCitizenProject($queryBuilder, $citizenProject)
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countTotalCommitteeMessage(Adherent $adherent, Committee $committee): int
    {
        $queryBuilder = $this
            ->createQueryBuilder('message')
            ->select('COUNT(message.id)')
        ;

        $this
            ->withMessageType($queryBuilder, AdherentMessageTypeEnum::COMMITTEE)
            ->withAuthor($queryBuilder, $adherent)
            ->withCommittee($queryBuilder, $committee)
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
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

    private function withCitizenProject(
        QueryBuilder $queryBuilder,
        CitizenProject $citizenProject,
        string $alias = 'message'
    ): self {
        $queryBuilder
            ->innerJoin(CitizenProjectFilter::class, 'filter', Join::WITH, "$alias.filter = filter")
            ->andWhere('filter.citizenProject = :citizen_project')
            ->setParameter('citizen_project', $citizenProject)
        ;

        return $this;
    }

    private function withCommittee(QueryBuilder $queryBuilder, Committee $committee, string $alias = 'message'): self
    {
        $queryBuilder
            ->innerJoin(CommitteeFilter::class, 'filter', Join::WITH, "$alias.filter = filter")
            ->andWhere('filter.committee = :committee')
            ->setParameter('committee', $committee)
        ;

        return $this;
    }

    private function orderByDate(QueryBuilder $queryBuilder, string $alias = 'message', string $order = 'DESC'): self
    {
        $queryBuilder->orderBy("$alias.createdAt", $order);

        return $this;
    }
}
