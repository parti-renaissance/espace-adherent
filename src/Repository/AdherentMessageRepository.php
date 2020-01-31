<?php

namespace AppBundle\Repository;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator as ApiPaginator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\AdherentMessage\AdherentMessageStatusEnum;
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

    public function removeAuthorItems(Adherent $author): void
    {
        $queryBuilder = $this->createQueryBuilder('message');

        $this->withAuthor($queryBuilder, $author);

        foreach ($queryBuilder->getQuery()->iterate() as $message) {
            $this->getEntityManager()->remove($message[0]);
        }

        $this->getEntityManager()->flush();
    }

    public function getLastSentMessage(Adherent $adherent, string $type): ?AdherentMessageInterface
    {
        $queryBuilder = $this
            ->createQueryBuilder('message')
            ->addSelect('campaign')
            ->addSelect('report')
            ->innerJoin('message.mailchimpCampaigns', 'campaign')
            ->innerJoin('campaign.report', 'report')
        ;

        $this
            ->withAuthor($queryBuilder, $adherent)
            ->withMessageType($queryBuilder, $type)
            ->orderByDate($queryBuilder)
            ->withStatus($queryBuilder, AdherentMessageStatusEnum::SENT_SUCCESSFULLY)
        ;

        return $queryBuilder
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
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
        return $this->configurePaginator($this->createListQueryBuilder($adherent, $type, $status), $page);
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
        $queryBuilder = $this->createListQueryBuilder($adherent, AdherentMessageTypeEnum::COMMITTEE, $status);

        $this->withCommittee($queryBuilder, $committee);

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
        $queryBuilder = $this->createListQueryBuilder($adherent, AdherentMessageTypeEnum::CITIZEN_PROJECT, $status);

        $this->withCitizenProject($queryBuilder, $citizenProject);

        return $this->configurePaginator($queryBuilder, $page);
    }

    public function countTotalMessage(Adherent $adherent, string $type, bool $currentMonthOnly = false): int
    {
        return (int) $this
            ->createCountQueryBuilder($adherent, $type, $currentMonthOnly)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countTotalCitizenProjectMessage(
        Adherent $adherent,
        CitizenProject $citizenProject,
        bool $currentMonthOnly = false
    ): int {
        $queryBuilder = $this->createCountQueryBuilder(
            $adherent,
            AdherentMessageTypeEnum::CITIZEN_PROJECT,
            $currentMonthOnly
        );

        $this->withCitizenProject($queryBuilder, $citizenProject);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countTotalCommitteeMessage(
        Adherent $adherent,
        Committee $committee,
        bool $currentMonthOnly = false
    ): int {
        $queryBuilder = $this->createCountQueryBuilder(
            $adherent,
            AdherentMessageTypeEnum::COMMITTEE,
            $currentMonthOnly
        );

        $this->withCommittee($queryBuilder, $committee);

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

    private function forCurrentMonthOnly(QueryBuilder $queryBuilder, string $alias = 'message'): self
    {
        $now = new \DateTime();

        $queryBuilder
            ->andWhere("$alias.sentAt >= :start_date AND $alias.sentAt <= :end_date")
            ->setParameter('start_date', ($now->modify('first day of this month')->format('Y-m-d 00:00:00')))
            ->setParameter('end_date', ($now->modify('last day of this month')->format('Y-m-d 23:59:59')))
        ;

        return $this;
    }

    private function createCountQueryBuilder(Adherent $adherent, string $type, bool $currentMonthOnly): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('message')
            ->select('COUNT(message.id)')
        ;

        $this
            ->withAuthor($queryBuilder, $adherent)
            ->withMessageType($queryBuilder, $type)
        ;

        if ($currentMonthOnly) {
            $this->forCurrentMonthOnly($queryBuilder);
        }

        return $queryBuilder;
    }

    private function createListQueryBuilder(Adherent $adherent, string $type, string $status = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('message')
            ->leftJoin('message.mailchimpCampaigns', 'mc')
            ->leftJoin('message.filter', 'f')
            ->addSelect('mc', 'f')
        ;

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

        return $queryBuilder;
    }
}
