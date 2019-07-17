<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Mailchimp\Exception\InvalidFilterException;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;

class AdherentZoneConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof AdherentZoneFilter;
    }

    /**
     * @var AdherentZoneFilter
     */
    protected function getSegmentId(AdherentMessageFilterInterface $filter): int
    {
        if (!$tag = $filter->getReferentTag()) {
            throw new InvalidFilterException($filter->getMessage(), '[AdherentMessage] Referent tag should not be empty');
        }

        if (!$tag->getExternalId()) {
            throw new StaticSegmentIdMissingException(
                sprintf('[AdherentMessage] Referent tag (%s) does not have a Mailchimp ID', $tag->getCode())
            );
        }

        return $tag->getExternalId();
    }
}
