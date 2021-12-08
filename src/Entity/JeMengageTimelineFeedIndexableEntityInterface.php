<?php

namespace App\Entity;

interface JeMengageTimelineFeedIndexableEntityInterface extends AlgoliaIndexedEntityInterface
{
    public function isTimelineFeedIndexable(): bool;
}
