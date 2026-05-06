<?php

declare(strict_types=1);

namespace App\Repository\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessageTargeted;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\TargetedProcessingStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentMessageTargetedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentMessageTargeted::class);
    }

    /**
     * Returns pushable pending rows of a chunk: rows with adherent_id NULL (cascade SET NULL after
     * adherent deletion) are excluded by the INNER JOIN — they cannot be pushed anymore.
     *
     * @return array<int, string> map id => email
     */
    public function findPendingEmailsByChunk(int $messageId, int $chunkNumber): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('t.id, a.emailAddress AS email')
            ->innerJoin('t.adherent', 'a')
            ->where('IDENTITY(t.message) = :messageId')
            ->andWhere('t.chunkNumber = :chunkNumber')
            ->andWhere('t.processingStatus = :pending')
            ->setParameter('messageId', $messageId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->setParameter('pending', TargetedProcessingStatusEnum::Pending)
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
     * @param array<int, TargetedProcessingStatusEnum> $idToStatus row id => status
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
            $status = TargetedProcessingStatusEnum::from($statusValue);

            $qb = $em->createQueryBuilder()
                ->update(AdherentMessageTargeted::class, 't')
                ->set('t.processingStatus', ':status')
                ->set('t.processedAt', ':now')
                ->where('t.id IN (:ids)')
                ->setParameter('status', $status)
                ->setParameter('now', $now)
                ->setParameter('ids', $ids)
            ;

            if (TargetedProcessingStatusEnum::Errored === $status && null !== $errorMessage) {
                $qb
                    ->set('t.errorMessage', ':errorMessage')
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
    public function markChunkAsErrored(int $messageId, int $chunkNumber, ?string $errorMessage): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->update(AdherentMessageTargeted::class, 't')
            ->set('t.processingStatus', ':errored')
            ->set('t.processedAt', ':now')
            ->set('t.errorMessage', ':message')
            ->where('IDENTITY(t.message) = :messageId')
            ->andWhere('t.chunkNumber = :chunkNumber')
            ->andWhere('t.processingStatus = :pending')
            ->setParameter('errored', TargetedProcessingStatusEnum::Errored)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('message', $errorMessage)
            ->setParameter('messageId', $messageId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->setParameter('pending', TargetedProcessingStatusEnum::Pending)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Same as markChunkAsErrored but resolves messageId from a MailchimpCampaign id. Used by
     * AudienceChunkFailureSubscriber which only receives the campaign id in the message payload.
     */
    public function markChunkAsErroredByCampaignId(int $campaignId, int $chunkNumber, ?string $errorMessage): int
    {
        $row = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(c.message) AS messageId')
            ->from(MailchimpCampaign::class, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $row) {
            return 0;
        }

        return $this->markChunkAsErrored((int) $row['messageId'], $chunkNumber, $errorMessage);
    }

    /**
     * Returns true only if at least one *pushable* pending row remains. Orphan rows (adherent_id
     * NULL after cascade SET NULL) are excluded by the INNER JOIN — they would never be processed
     * and must not block the finalize handler.
     */
    public function existsPending(int $messageId): bool
    {
        $row = $this->createQueryBuilder('t')
            ->select('t.id')
            ->innerJoin('t.adherent', 'a')
            ->where('IDENTITY(t.message) = :messageId')
            ->andWhere('t.processingStatus = :pending')
            ->setParameter('messageId', $messageId)
            ->setParameter('pending', TargetedProcessingStatusEnum::Pending)
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
    public function findChunksWithPending(int $messageId): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('DISTINCT t.chunkNumber')
            ->innerJoin('t.adherent', 'a')
            ->where('IDENTITY(t.message) = :messageId')
            ->andWhere('t.processingStatus = :pending')
            ->setParameter('messageId', $messageId)
            ->setParameter('pending', TargetedProcessingStatusEnum::Pending)
            ->orderBy('t.chunkNumber', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(static function (array $row): int {
            return (int) $row['chunkNumber'];
        }, $rows);
    }

    /**
     * Purge all targeted rows for a given message. Called by the orchestrator before a fresh
     * bulk insert to avoid residual rows from a previous (legacy) preparation run.
     */
    public function deleteByMessageId(int $messageId): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->delete(AdherentMessageTargeted::class, 't')
            ->where('IDENTITY(t.message) = :messageId')
            ->setParameter('messageId', $messageId)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return array<string, int> status value => count
     */
    public function aggregateStatusCounts(int $messageId): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('t.processingStatus AS status, COUNT(t.id) AS cnt')
            ->where('IDENTITY(t.message) = :messageId')
            ->setParameter('messageId', $messageId)
            ->groupBy('t.processingStatus')
            ->getQuery()
            ->getArrayResult()
        ;

        $result = [];
        foreach ($rows as $row) {
            $status = $row['status'] instanceof TargetedProcessingStatusEnum
                ? $row['status']->value
                : (string) $row['status'];
            $result[$status] = (int) $row['cnt'];
        }

        return $result;
    }
}
