<?php

namespace App\Repository;

use App\Adherent\Tag\TagEnum;
use App\Entity\Action\Action;
use App\Entity\Action\ActionParticipant;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Entity\NotificationObjectInterface;
use App\Entity\PushToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PushTokenRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PushToken::class);
    }

    public function findByIdentifier(string $identifier): ?PushToken
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    public function findAllForZone(Zone $zone): array
    {
        $queryBuilder = $this->createIdentifierQueryBuilder('t');

        $this->withGeoZones(
            [$zone],
            $queryBuilder,
            'a',
            Adherent::class,
            'a2',
            'zones',
            'z'
        );

        return $queryBuilder->getQuery()->getSingleColumnResult();
    }

    public function findAllForNotificationObject(NotificationObjectInterface $object): array
    {
        $queryBuilder = $this->createIdentifierQueryBuilder('t');

        $filterEnabled = false;

        if ($object instanceof Event && !$object->getCommittee()) {
            $filterEnabled = true;

            $queryBuilder
                ->innerJoin(EventRegistration::class, 'er', Join::WITH, 'er.adherentUuid = a.uuid')
                ->andWhere('er.event = :event')
                ->setParameter('event', $object)
            ;
        } elseif (($object instanceof Event && $object->getCommittee()) || $object instanceof News) {
            $filterEnabled = true;

            $queryBuilder
                ->innerJoin('a.committeeMembership', 'cm')
                ->andWhere('cm.committee = :committee')
                ->andWhere('a.tags LIKE :adherent_tag')
                ->setParameter('adherent_tag', TagEnum::ADHERENT.'%')
                ->setParameter('committee', $object->getCommittee())
            ;
        } elseif ($object instanceof Action) {
            $filterEnabled = true;

            $queryBuilder
                ->innerJoin(ActionParticipant::class, 'ap', Join::WITH, 'ap.adherent = a')
                ->andWhere('ap.action = :action')
                ->setParameter('action', $object)
            ;
        }

        if (!$filterEnabled) {
            return [];
        }

        return $queryBuilder->getQuery()->getSingleColumnResult();
    }

    public function findAllForNational(): array
    {
        return $this->createIdentifierQueryBuilder('t')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    private function createIdentifierQueryBuilder(string $alias): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->select(\sprintf('DISTINCT %s.identifier', $alias))
            ->innerJoin($alias.'.adherent', 'a')
            ->andWhere('a.status = :enabled')
            ->setParameter('enabled', Adherent::ENABLED)
        ;
    }
}
