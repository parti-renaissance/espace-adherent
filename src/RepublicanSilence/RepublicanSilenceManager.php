<?php

namespace App\RepublicanSilence;

use App\Address\Address;
use App\Entity\ReferentTag;
use App\Entity\RepublicanSilence;
use App\Repository\ReferentTagRepository;
use App\Repository\RepublicanSilenceRepository;

class RepublicanSilenceManager
{
    private $repository;

    public function __construct(RepublicanSilenceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return RepublicanSilence[]
     */
    public function getRepublicanSilencesForDate(\DateTimeInterface $date): iterable
    {
        return $this->repository->findStarted($date);
    }

    /**
     * @return RepublicanSilence[]
     */
    public function getRepublicanSilencesFromDate(\DateTimeInterface $date): iterable
    {
        return $this->repository->findFromDate($date);
    }

    public function hasStartedSilence(array $referentTagCodes = null): bool
    {
        $silences = $this->getRepublicanSilencesForDate(new \DateTime());

        if (null === $referentTagCodes) {
            return !empty($silences);
        }

        foreach ($silences as $silence) {
            if ($this->matchSilence($silence, $referentTagCodes)) {
                return true;
            }
        }

        return false;
    }

    private function matchSilence(RepublicanSilence $silence, array $referentTagCodes): bool
    {
        if (array_intersect($silence->getReferentTagCodes(), $referentTagCodes)) {
            return true;
        }

        if (
            \in_array(ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG, $referentTagCodes, true)
            && !$silence->getReferentTags()->filter(static function (ReferentTag $tag) {
                return ReferentTag::TYPE_COUNTRY === $tag->getType()
                    && Address::FRANCE !== $tag->getCode();
            })->isEmpty()
        ) {
            return true;
        }

        return false;
    }
}
