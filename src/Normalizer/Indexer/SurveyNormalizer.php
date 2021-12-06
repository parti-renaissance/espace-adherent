<?php

namespace App\Normalizer\Indexer;

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
}
