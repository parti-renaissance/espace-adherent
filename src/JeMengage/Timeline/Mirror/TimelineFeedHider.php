<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror;

use App\Entity\Timeline\TimelineHiddenFeed;
use App\Repository\Timeline\TimelineHiddenFeedRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TimelineFeedHider
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TimelineHiddenFeedRepository $hiddenRepository,
        private readonly TimelineFeedWriter $writer,
    ) {
    }

    public function hide(Uuid $uuid): void
    {
        if (!$this->hiddenRepository->isHidden($uuid)) {
            try {
                $this->entityManager->persist(new TimelineHiddenFeed($uuid));
                $this->entityManager->flush();
            } catch (UniqueConstraintViolationException) {
            }
        }

        $this->writer->delete($uuid);
    }
}
