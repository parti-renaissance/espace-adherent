<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\Jecoute\Survey;

class SurveyNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return Survey::class;
    }

    /** @param Survey $object */
    protected function getTitle(object $object): string
    {
        return $object->getName();
    }

    /** @param Survey $object */
    protected function getDescription(object $object): ?string
    {
        return \sprintf('%s questions', $object->getQuestionsCount());
    }

    /** @param Survey $object */
    protected function isNational(object $object): bool
    {
        return $object->isNational();
    }

    /** @param Survey $object */
    protected function getDate(object $object): ?\DateTimeInterface
    {
        return $object->getCreatedAt();
    }

    /** @param Survey $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return $object->getCreator();
    }

    /** @param Survey $object */
    protected function getZoneCodes(object $object): ?array
    {
        return $this->buildZoneCodes($object->isLocal() ? $object->getZone() : null);
    }
}
