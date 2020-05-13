<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\InvalidFilterException;
use App\Mailchimp\Exception\StaticSegmentIdMissingException;

class AdherentZoneConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof AdherentZoneFilter;
    }

    /**
     * @param AdherentZoneFilter $filter
     */
    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        if (!$tag = $filter->getReferentTag()) {
            throw new InvalidFilterException($filter->getMessage(), '[AdherentMessage] Referent tag should not be empty');
        }

        if (!$tag->getExternalId()) {
            throw new StaticSegmentIdMissingException(sprintf('[AdherentMessage] Referent tag (%s) does not have a Mailchimp ID', $tag->getCode()));
        }

        return $tag->getExternalId();
    }
}
