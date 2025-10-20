<?php

namespace App\Repository;

use App\AppSession\SessionStatusEnum;
use App\AppSession\SystemEnum;
use App\Entity\AppSession;
use App\Entity\OAuth\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class AppSessionRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppSession::class);
    }

    public function countActiveSessions(Client $cadreClient, ?SystemEnum $appSystem = null): int
    {
        $criteria = [
            'client' => $cadreClient,
            'status' => SessionStatusEnum::ACTIVE,
        ];

        if ($appSystem) {
            $criteria['appSystem'] = $appSystem;
        }

        return $this->count($criteria);
    }

    public function countActivePushTokens(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(DISTINCT s.id)')
            ->innerJoin('s.pushTokenLinks', 'link', Join::WITH, 'link.unsubscribedAt IS NULL')
            ->innerJoin('link.pushToken', 'token')
            ->where('s.status = :status AND s.unsubscribedAt IS NULL')
            ->andWhere('s.adherent IS NOT NULL')
            ->setParameter('status', SessionStatusEnum::ACTIVE)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function terminateStaleSessions(): void
    {
        $this->createQueryBuilder('s')
            ->update()
            ->set('s.status', ':status')
            ->where('s.status = :active_status AND s.lastActivityDate < :date')
            ->setParameter('status', SessionStatusEnum::TERMINATED)
            ->setParameter('active_status', SessionStatusEnum::ACTIVE)
            ->setParameter('date', (new \DateTimeImmutable())->modify('-1 month'))
            ->getQuery()
            ->execute()
        ;
    }

    public function findByPushToken(string $pushToken): ?AppSession
    {
        return $this->createQueryBuilder('s')
            ->addSelect('a')
            ->innerJoin('s.adherent', 'a')
            ->innerJoin('s.pushTokenLinks', 'link')
            ->innerJoin('link.pushToken', 'token', Join::WITH, 'token.token = :push_token')
            ->setParameter('push_token', $pushToken)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
