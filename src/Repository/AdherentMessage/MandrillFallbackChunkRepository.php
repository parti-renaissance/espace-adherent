<?php

declare(strict_types=1);

namespace App\Repository\AdherentMessage;

use App\Entity\AdherentMessage\MandrillFallbackChunk;
use App\Mailchimp\Campaign\Fallback\MandrillFallbackChunkStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MandrillFallbackChunkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MandrillFallbackChunk::class);
    }

    public function claimForSending(int $campaignId, int $chunkNumber): bool
    {
        $affected = (int) $this->getEntityManager()->createQueryBuilder()
            ->update(MandrillFallbackChunk::class, 'c')
            ->set('c.status', ':sending')
            ->where('IDENTITY(c.campaign) = :campaignId')
            ->andWhere('c.chunkNumber = :chunkNumber')
            ->andWhere('c.status = :pending')
            ->setParameter('sending', MandrillFallbackChunkStatusEnum::Sending)
            ->setParameter('campaignId', $campaignId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->setParameter('pending', MandrillFallbackChunkStatusEnum::Pending)
            ->getQuery()
            ->execute()
        ;

        return 1 === $affected;
    }

    public function markSent(int $campaignId, int $chunkNumber): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(MandrillFallbackChunk::class, 'c')
            ->set('c.status', ':sent')
            ->set('c.sentAt', ':now')
            ->where('IDENTITY(c.campaign) = :campaignId')
            ->andWhere('c.chunkNumber = :chunkNumber')
            ->setParameter('sent', MandrillFallbackChunkStatusEnum::Sent)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('campaignId', $campaignId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->getQuery()
            ->execute()
        ;
    }

    public function markNeedsReview(int $campaignId, int $chunkNumber): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(MandrillFallbackChunk::class, 'c')
            ->set('c.status', ':needsReview')
            ->where('IDENTITY(c.campaign) = :campaignId')
            ->andWhere('c.chunkNumber = :chunkNumber')
            ->setParameter('needsReview', MandrillFallbackChunkStatusEnum::NeedsReview)
            ->setParameter('campaignId', $campaignId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->getQuery()
            ->execute()
        ;
    }

    public function markPending(int $campaignId, int $chunkNumber): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(MandrillFallbackChunk::class, 'c')
            ->set('c.status', ':pending')
            ->where('IDENTITY(c.campaign) = :campaignId')
            ->andWhere('c.chunkNumber = :chunkNumber')
            ->setParameter('pending', MandrillFallbackChunkStatusEnum::Pending)
            ->setParameter('campaignId', $campaignId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->getQuery()
            ->execute()
        ;
    }

    public function findStatus(int $campaignId, int $chunkNumber): ?MandrillFallbackChunkStatusEnum
    {
        $status = $this->createQueryBuilder('c')
            ->select('c.status')
            ->where('IDENTITY(c.campaign) = :campaignId')
            ->andWhere('c.chunkNumber = :chunkNumber')
            ->setParameter('campaignId', $campaignId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $status['status'] ?? null;
    }
}
