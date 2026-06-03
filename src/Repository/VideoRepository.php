<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 *
 * @use UuidEntityRepositoryTrait<Video>
 */
class VideoRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    /**
     * Atomically sets transcodeWithoutAudio on a video that still has it false, returning whether this
     * call won the flip. The status poll message carries no lock, so this guards against two concurrent
     * FAILED polls both triggering the no-audio retry: only the winner relaunches.
     */
    public function flagTranscodeWithoutAudio(Video $video): bool
    {
        return $this->getEntityManager()
            ->createQuery(
                'UPDATE '.Video::class.' v SET v.transcodeWithoutAudio = true WHERE v = :video AND v.transcodeWithoutAudio = false'
            )
            ->setParameter('video', $video)
            ->execute() > 0;
    }

    /**
     * Counts videos with an active transcoding job (status PROCESSING), excluding the given one. Used by
     * the capacity gate as a cheap, conservative proxy for the GCP concurrent-job quota. The exclusion
     * matters for the no-audio retry, where the video is still PROCESSING while its GCP job has already
     * failed: it must not count itself as an active slot.
     */
    public function countActiveTranscodingJobs(Video $excluding): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(v.id) FROM '.Video::class.' v WHERE v.status = :status AND v != :excluding'
            )
            ->setParameter('status', VideoStatusEnum::PROCESSING)
            ->setParameter('excluding', $excluding)
            ->getSingleScalarResult();
    }
}
