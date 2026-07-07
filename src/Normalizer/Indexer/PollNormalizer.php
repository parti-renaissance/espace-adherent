<?php

declare(strict_types=1);

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\Poll\Poll;

class PollNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return Poll::class;
    }

    /** @param Poll $object */
    protected function getTitle(object $object): string
    {
        return (string) $object->getQuestion();
    }

    /** @param Poll $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getDescription();
    }

    /** @param Poll $object */
    protected function getDate(object $object): ?\DateTimeInterface
    {
        return $object->getStartAt();
    }

    /** @param Poll $object */
    protected function getBeginAt(object $object): ?\DateTimeInterface
    {
        return $object->getStartAt();
    }

    /** @param Poll $object */
    protected function getFinishAt(object $object): ?\DateTimeInterface
    {
        return $object->getFinishAt();
    }

    /** @param Poll $object */
    protected function isNational(object $object): bool
    {
        return true;
    }

    protected function getCtaLabel(object $object): ?string
    {
        return 'Je participe';
    }

    protected function getAuthorObject(object $object): ?Adherent
    {
        return null;
    }
}
