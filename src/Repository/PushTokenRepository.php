<?php

declare(strict_types=1);

namespace App\Repository;

use App\Adherent\Tag\TagEnum;
use App\AppSession\SessionStatusEnum;
use App\Entity\Action\Action;
use App\Entity\Action\ActionParticipant;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AppSessionPushTokenLink;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NotificationObjectInterface;
use App\Entity\PushToken;
use App\Entity\TimelineItemPrivateMessage;
use App\JeMengage\Push\Command\NationalEventTicketAvailableNotificationCommand;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\PushToken>
 */
class PushTokenRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;
    use AudienceFilterTrait;

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

    public function findAllForNotificationObject(NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        $queryBuilder = $this->createIdentifierQueryBuilder('t');

        $filterEnabled = false;

        if ($object instanceof Event && !$object->getCommittee()) {
            $filterEnabled = true;

            $queryBuilder
                ->innerJoin(EventRegistration::class, 'er', Join::WITH, 'er.adherent = a')
                ->andWhere('er.event = :event')
                ->setParameter('event', $object)
            ;
        } elseif ($object instanceof TimelineItemPrivateMessage) {
            $filterEnabled = true;

            $queryBuilder
                ->innerJoin(TimelineItemPrivateMessage::class, 'pm')
                ->innerJoin('pm.adherents', 'a2', Join::WITH, 'a2 = a')
                ->andWhere('pm = :private_message')
                ->setParameter('private_message', $object)
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
        } elseif ($object instanceof NationalEvent) {
            $filterEnabled = true;

            $queryBuilder
                ->innerJoin(EventInscription::class, 'ie', Join::WITH, 'ie.adherent = a')
                ->andWhere('ie.event = :event')
                ->setParameter('event', $object)
            ;

            if ($command instanceof NationalEventTicketAvailableNotificationCommand && 'all' !== $command->destinationType) {
                if (Uuid::isValid($command->destinationType)) {
                    $queryBuilder
                        ->andWhere('ie.uuid = :inscription_uuid')
                        ->setParameter('inscription_uuid', $command->destinationType)
                    ;
                } else {
                    $queryBuilder->andWhere('ie.pushSentAt IS NULL');
                }
            }
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

    public function findAllForAdherentMessage(AdherentMessage $message): array
    {
        /** @var AudienceFilter $filter */
        $filter = $message->getFilter();

        $qb = $this->createIdentifierQueryBuilder('t', $adherentAlias = 'a');

        $this->applyAudienceFilter($filter, $qb, $adherentAlias);

        return $qb->getQuery()->getSingleColumnResult();
    }

    public function findAllIdsForNational(): array
    {
        $result = $this->createIdentifierQueryBuilder('t')
            ->select('DISTINCT t.id')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_column($result, 'id');
    }

    private function createIdentifierQueryBuilder(string $alias, string $adherentAlias = 'a'): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->select(\sprintf('DISTINCT %s.identifier', $alias))
            ->innerJoin(AppSessionPushTokenLink::class, 'link', Join::WITH, 'link.pushToken = '.$alias)
            ->innerJoin('link.appSession', 's', Join::WITH, 's.status = :session_status AND s.unsubscribedAt IS NULL')
            ->innerJoin('s.adherent', $adherentAlias)
            ->andWhere($adherentAlias.'.status = :enabled')
            ->orderBy($alias.'.identifier')
            ->setParameter('enabled', Adherent::ENABLED)
            ->setParameter('session_status', SessionStatusEnum::ACTIVE)
        ;
    }

    /**
     * @return string[]
     */
    public function findAllIdentifiersByIds(array $ids): array
    {
        return $this->createIdentifierQueryBuilder('t')
            ->andWhere('t.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
