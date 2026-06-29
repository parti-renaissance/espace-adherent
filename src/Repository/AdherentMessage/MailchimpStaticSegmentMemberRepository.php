<?php

declare(strict_types=1);

namespace App\Repository\AdherentMessage;

use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Contact\ContactStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

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
     * @param array<int, SegmentMemberStatusEnum> $idToStatus       row id => status
     * @param array<int, string>                  $idToErrorMessage row id => Mailchimp error message
     *                                                              (only set for refused/errored rows)
     */
    public function markRowsAsProcessed(array $idToStatus, array $idToErrorMessage = []): void
    {
        if ([] === $idToStatus) {
            return;
        }

        // Group rows by (status, errorMessage) so each unique combination becomes a single UPDATE.
        // In practice Mailchimp returns the same error message for all refused rows of a chunk,
        // so this typically collapses to 1-2 buckets.
        $buckets = [];
        foreach ($idToStatus as $id => $status) {
            $errorMessage = $idToErrorMessage[$id] ?? null;
            $key = $status->value.'|'.($errorMessage ?? '');
            if (!isset($buckets[$key])) {
                $buckets[$key] = ['status' => $status, 'errorMessage' => $errorMessage, 'ids' => []];
            }
            $buckets[$key]['ids'][] = $id;
        }

        $em = $this->getEntityManager();
        $now = new \DateTimeImmutable();

        foreach ($buckets as $bucket) {
            $qb = $em->createQueryBuilder()
                ->update(MailchimpStaticSegmentMember::class, 'm')
                ->set('m.processingStatus', ':status')
                ->set('m.processedAt', ':now')
                ->where('m.id IN (:ids)')
                ->setParameter('status', $bucket['status'])
                ->setParameter('now', $now)
                ->setParameter('ids', $bucket['ids'])
            ;

            if (null !== $bucket['errorMessage']) {
                $qb
                    ->set('m.errorMessage', ':errorMessage')
                    ->setParameter('errorMessage', $bucket['errorMessage'])
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
            ->select('s.id AS staticSegmentId')
            ->from(MailchimpStaticSegment::class, 's')
            ->where('IDENTITY(s.campaign) = :id')
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $row) {
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

    /**
     * Distinct chunk numbers still holding at least one sendable row (Added + consented). Drives the
     * SES fan-out: only chunks with real work get a SendSesCampaignChunkMessage.
     *
     * @return list<int>
     */
    public function findChunkNumbersToSend(int $staticSegmentId): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('DISTINCT m.chunkNumber')
            ->innerJoin('m.adherent', 'a')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->andWhere('m.processingStatus = :added')
            ->andWhere('a.mailchimpStatus = :subscribed')
            ->andWhere('a.emailHardBouncedAt IS NULL')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->setParameter('added', SegmentMemberStatusEnum::Added)
            ->setParameter('subscribed', ContactStatusEnum::SUBSCRIBED)
            ->orderBy('m.chunkNumber', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(static function (array $row): int {
            return (int) $row['chunkNumber'];
        }, $rows);
    }

    /**
     * Sendable rows of a chunk: status Added + adherent still consented. Includes the row id so the
     * worker can claim each row individually (per-recipient at-most-once).
     *
     * @return list<array{id: int, email: string, uuid: Uuid, firstName: ?string, lastName: ?string, gender: ?string, publicId: ?string}>
     */
    public function findClaimableRecipientsByChunk(int $staticSegmentId, int $chunkNumber): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.id, a.emailAddress AS email, a.uuid, a.firstName, a.lastName, a.gender, a.publicId')
            ->innerJoin('m.adherent', 'a')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->andWhere('m.chunkNumber = :chunkNumber')
            ->andWhere('m.processingStatus = :added')
            ->andWhere('a.mailchimpStatus = :subscribed')
            ->andWhere('a.emailHardBouncedAt IS NULL')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->setParameter('chunkNumber', $chunkNumber)
            ->setParameter('added', SegmentMemberStatusEnum::Added)
            ->setParameter('subscribed', ContactStatusEnum::SUBSCRIBED)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function claimRowForSending(int $rowId): bool
    {
        $affected = (int) $this->getEntityManager()->createQueryBuilder()
            ->update(MailchimpStaticSegmentMember::class, 'm')
            ->set('m.processingStatus', ':sending')
            ->where('m.id = :id')
            ->andWhere('m.processingStatus = :added')
            ->setParameter('sending', SegmentMemberStatusEnum::Sending)
            ->setParameter('id', $rowId)
            ->setParameter('added', SegmentMemberStatusEnum::Added)
            ->getQuery()
            ->execute()
        ;

        return 1 === $affected;
    }

    /**
     * Marks a claimed row delivered (Sending -> Sent). Guarded on Sending so a stray call cannot
     * resurrect a row from another state.
     */
    public function markRowSent(int $rowId): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(MailchimpStaticSegmentMember::class, 'm')
            ->set('m.processingStatus', ':sent')
            ->set('m.processedAt', ':now')
            ->where('m.id = :id')
            ->andWhere('m.processingStatus = :sending')
            ->setParameter('sent', SegmentMemberStatusEnum::Sent)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('id', $rowId)
            ->setParameter('sending', SegmentMemberStatusEnum::Sending)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Reopens a claimed row after a transport failure (Sending -> Added) so a Messenger retry can
     * pick it up again. The send did not happen, so no duplicate results from reopening.
     */
    public function reopenRow(int $rowId): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(MailchimpStaticSegmentMember::class, 'm')
            ->set('m.processingStatus', ':added')
            ->where('m.id = :id')
            ->andWhere('m.processingStatus = :sending')
            ->setParameter('added', SegmentMemberStatusEnum::Added)
            ->setParameter('id', $rowId)
            ->setParameter('sending', SegmentMemberStatusEnum::Sending)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Adherent ids of the rows actually sent for this segment. Source of the campaign reach
     * (red-team #4): no provider report is polled, the reach is derived from what was really sent.
     * Orphan rows (adherent_id NULL after cascade SET NULL) are excluded by the INNER JOIN.
     *
     * @return list<int>
     */
    public function findSentAdherentIds(int $staticSegmentId): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('IDENTITY(m.adherent) AS adherentId')
            ->innerJoin('m.adherent', 'a')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->andWhere('m.processingStatus = :sent')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->setParameter('sent', SegmentMemberStatusEnum::Sent)
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(static function (array $row): int {
            return (int) $row['adherentId'];
        }, $rows);
    }

    /**
     * Count of rows that still represent sendable work for the whole segment: any in-flight Sending,
     * plus Added rows whose adherent is still consented. Reaching 0 means the campaign send is
     * complete (Added-but-unsubscribed leftovers never block completion, as they are never sent).
     */
    public function countRemainingToSend(int $staticSegmentId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->innerJoin('m.adherent', 'a')
            ->where('IDENTITY(m.staticSegment) = :staticSegmentId')
            ->andWhere('m.processingStatus = :sending OR (m.processingStatus = :added AND a.mailchimpStatus = :subscribed AND a.emailHardBouncedAt IS NULL)')
            ->setParameter('staticSegmentId', $staticSegmentId)
            ->setParameter('sending', SegmentMemberStatusEnum::Sending)
            ->setParameter('added', SegmentMemberStatusEnum::Added)
            ->setParameter('subscribed', ContactStatusEnum::SUBSCRIBED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
