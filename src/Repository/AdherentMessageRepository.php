<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\Committee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class AdherentMessageRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

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

    /**
     * @return AdherentMessageInterface[]|PaginatorInterface
     */
    public function findAllByAuthor(
        Adherent $adherent,
        string $type,
        ?string $status = null,
        int $page = 1,
    ): PaginatorInterface {
        return $this->configurePaginator($this->createListQueryBuilder($adherent, $type, $status), $page);
    }

    /**
     * @return CommitteeAdherentMessage[]|PaginatorInterface
     */
    public function findAllCommitteeMessage(
        Adherent $adherent,
        Committee $committee,
        ?string $status = null,
        int $page = 1,
    ): PaginatorInterface {
        $queryBuilder = $this->createListQueryBuilder($adherent, AdherentMessageTypeEnum::COMMITTEE, $status);

        $this->withCommittee($queryBuilder, $committee);

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

    public function countTotalCommitteeMessage(
        Adherent $adherent,
        Committee $committee,
        bool $currentMonthOnly = false,
    ): int {
        $queryBuilder = $this->createCountQueryBuilder(
            $adherent,
            AdherentMessageTypeEnum::COMMITTEE,
            $currentMonthOnly
        );

        $this->withCommittee($queryBuilder, $committee);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function withMessageType(QueryBuilder $queryBuilder, string $messageType, string $alias = 'message'): self
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

    private function withCommittee(QueryBuilder $queryBuilder, Committee $committee, string $alias = 'message'): self
    {
        $queryBuilder
            ->innerJoin(MessageFilter::class, 'filter', Join::WITH, "$alias.filter = filter")
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
            ->setParameter('start_date', $now->modify('first day of this month')->format('Y-m-d 00:00:00'))
            ->setParameter('end_date', $now->modify('last day of this month')->format('Y-m-d 23:59:59'))
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

    private function createListQueryBuilder(Adherent $adherent, string $type, ?string $status = null): QueryBuilder
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
            $this->withStatus($queryBuilder, $status);
        }

        return $queryBuilder;
    }
}
