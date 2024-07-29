<?php

namespace App\RepublicanSilence;

use App\Exception\InvalidAdherentTypeException;
use App\RepublicanSilence\ZoneExtractor\ZoneExtractorInterface;

class ZoneExtractorFactory
{
    /** @var iterable|ZoneExtractorInterface[] */
    private iterable $extractors;

    public function __construct(iterable $extractors)
    {
        $this->extractors = $extractors;
    }

    public function create(int $type): ZoneExtractorInterface
    {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($type)) {
                return $extractor;
            }
        }

        throw new InvalidAdherentTypeException(\sprintf('Adherent type [%d] is invalid', $type));
    }
}
