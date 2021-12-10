<?php

namespace App\Normalizer\Indexer;

use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\Survey;
use App\JeMengageTimelineFeed\JeMengageTimelineFeedEnum;

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

    protected function getType(): string
    {
        return JeMengageTimelineFeedEnum::SURVEY;
    }

    /** @param Survey $object */
    protected function getDescription(object $object): ?string
    {
        return sprintf('%s questions', $object->getQuestionsCount());
    }

    /** @param Survey $object */
    protected function isLocal(object $object): bool
    {
        return $object->isLocal();
    }

    /** @param Survey $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param Survey $object */
    protected function getAuthor(object $object): ?string
    {
        return is_a($object, LocalSurvey::class) && $object->getAuthor() ? $object->getAuthor()->getFullName() : null;
    }
}
