<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\InvalidFilterException;
use App\Mailchimp\Exception\StaticSegmentIdMissingException;

class CommitteeConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof CommitteeFilter
            || ($filter instanceof ReferentUserFilter && $filter->getCommittee())
            || ($filter instanceof AdherentZoneFilter && $filter->getCommittee())
        ;
    }

    /**
     * @param CommitteeFilter|ReferentUserFilter|AdherentZoneFilter $filter
     */
    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        if (!$committee = $filter->getCommittee()) {
            throw new InvalidFilterException($filter->getMessage(), '[AdherentMessage] Committee should not be empty');
        }

        if (!$committee->getMailchimpId()) {
            throw new StaticSegmentIdMissingException(sprintf('[AdherentMessage] Committee "%s" does not have mailchimp ID', $committee->getUuidAsString()));
        }

        return $committee->getMailchimpId();
    }
}
