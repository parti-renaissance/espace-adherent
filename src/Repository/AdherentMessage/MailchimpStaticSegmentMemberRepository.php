<?php

declare(strict_types=1);

namespace App\Repository\AdherentMessage;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MailchimpStaticSegmentMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpStaticSegmentMember::class);
    }

    /**
     * Returns pushable pending rows of a chunk: rows with adherent_id NULL (cascade SET NULL after
     * adherent deletion) are excluded by the INNER JOIN — they cannot be pushed anymore.
     *
     * @return array<int, string> map id => email
     */
    public function findPendingEmailsByChunk(int $staticSegmentId, int $chunkNumber): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.id, a.emailAddress AS email')
            ->innerJoin('m.adherent', 'a')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->andWhere('m.chunkNumber = :chunkNumber')
            ->andWhere('m.processingStatus = :pending')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->setParameter('pending', SegmentMemberStatusEnum::Pending)
            ->getQuery()
            ->getArrayResult()
        ;

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['id']] = (string) $row['email'];
        }

        return $result;
    }

    /**
     * Apply per-row processing status updates returned by Mailchimp push.
     *
     * @param array<int, SegmentMemberStatusEnum> $idToStatus row id => status
     */
    public function markRowsAsProcessed(array $idToStatus, ?string $errorMessage = null): void
    {
        if ([] === $idToStatus) {
            return;
        }

        $grouped = [];
        foreach ($idToStatus as $id => $status) {
            $grouped[$status->value][] = $id;
        }

        $em = $this->getEntityManager();
        $now = new \DateTimeImmutable();

        foreach ($grouped as $statusValue => $ids) {
            $status = SegmentMemberStatusEnum::from($statusValue);

            $qb = $em->createQueryBuilder()
                ->update(MailchimpStaticSegmentMember::class, 'm')
                ->set('m.processingStatus', ':status')
                ->set('m.processedAt', ':now')
                ->where('m.id IN (:ids)')
                ->setParameter('status', $status)
                ->setParameter('now', $now)
                ->setParameter('ids', $ids)
            ;

            if (SegmentMemberStatusEnum::Errored === $status && null !== $errorMessage) {
                $qb
                    ->set('m.errorMessage', ':errorMessage')
                    ->setParameter('errorMessage', $errorMessage)
                ;
            }

            $qb->getQuery()->execute();
        }
    }

    /**
     * Mark all pending rows of a chunk as errored. Called by the failure subscriber when
     * a ProcessAudienceChunkMessage exhausts its retries.
     */
    public function markChunkAsErrored(int $staticSegmentId, int $chunkNumber, ?string $errorMessage): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->update(MailchimpStaticSegmentMember::class, 'm')
            ->set('m.processingStatus', ':errored')
            ->set('m.processedAt', ':now')
            ->set('m.errorMessage', ':message')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->andWhere('m.chunkNumber = :chunkNumber')
            ->andWhere('m.processingStatus = :pending')
            ->setParameter('errored', SegmentMemberStatusEnum::Errored)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('message', $errorMessage)
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->setParameter('pending', SegmentMemberStatusEnum::Pending)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Same as markChunkAsErrored but resolves staticSegmentId from a MailchimpCampaign id. Used by
     * AudienceChunkFailureSubscriber which only receives the campaign id in the message payload.
     */
    public function markChunkAsErroredByCampaignId(int $campaignId, int $chunkNumber, ?string $errorMessage): int
    {
        $row = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(c.mailchimpStaticSegment) AS staticSegmentId')
            ->from(MailchimpCampaign::class, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $row || null === $row['staticSegmentId']) {
            return 0;
        }

        return $this->markChunkAsErrored((int) $row['staticSegmentId'], $chunkNumber, $errorMessage);
    }

    /**
     * Returns true only if at least one *pushable* pending row remains. Orphan rows (adherent_id
     * NULL after cascade SET NULL) are excluded by the INNER JOIN — they would never be processed
     * and must not block the finalize handler.
     */
    public function existsPending(int $staticSegmentId): bool
    {
        $row = $this->createQueryBuilder('m')
            ->select('m.id')
            ->innerJoin('m.adherent', 'a')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->andWhere('m.processingStatus = :pending')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->setParameter('pending', SegmentMemberStatusEnum::Pending)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return null !== $row;
    }

    /**
     * @return list<int> distinct chunk numbers having at least one pushable pending row.
     *                   Orphan rows (adherent_id NULL) are excluded by the INNER JOIN.
     */
    public function findChunksWithPending(int $staticSegmentId): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('DISTINCT m.chunkNumber')
            ->innerJoin('m.adherent', 'a')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->andWhere('m.processingStatus = :pending')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->setParameter('pending', SegmentMemberStatusEnum::Pending)
            ->orderBy('m.chunkNumber', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(static function (array $row): int {
            return (int) $row['chunkNumber'];
        }, $rows);
    }

    /**
     * Purge all member rows for a given static segment. Called by the orchestrator before a fresh
     * bulk insert to avoid residual rows from a previous (legacy) preparation run.
     */
    public function deleteBySegmentId(int $staticSegmentId): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->delete(MailchimpStaticSegmentMember::class, 'm')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return array<string, int> status value => count
     */
    public function aggregateStatusCounts(int $staticSegmentId): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.processingStatus AS status, COUNT(m.id) AS cnt')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->groupBy('m.processingStatus')
            ->getQuery()
            ->getArrayResult()
        ;

        $result = [];
        foreach ($rows as $row) {
            $status = $row['status'] instanceof SegmentMemberStatusEnum
                ? $row['status']->value
                : (string) $row['status'];
            $result[$status] = (int) $row['cnt'];
        }

        return $result;
    }
}
