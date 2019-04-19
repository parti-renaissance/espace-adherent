<?php

namespace AppBundle\Repository;

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

    /**
     * @return CommitteeAdherentMessage[]
     */
    public function findAllCommitteeMessage(
        Adherent $adherent,
        Committee $committee = null,
        string $status = null
    ): array {
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

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return CitizenProjectAdherentMessage[]
     */
    public function findAllCitizenProjectMessage(
        Adherent $adherent,
        CitizenProject $citizenProject,
        string $status = null
    ): array {
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

        return $queryBuilder->getQuery()->getResult();
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
}
