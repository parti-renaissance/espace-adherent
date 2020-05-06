<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\CitizenProjectFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\InvalidFilterException;
use App\Mailchimp\Exception\StaticSegmentIdMissingException;

class CitizenProjectConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof CitizenProjectFilter;
    }

    /**
     * @param CitizenProjectFilter $filter
     */
    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        if (!$citizenProject = $filter->getCitizenProject()) {
            throw new InvalidFilterException($filter->getMessage(), '[AdherentMessage] Citizen project should not be empty');
        }

        if (!$citizenProject->getMailchimpId()) {
            throw new StaticSegmentIdMissingException(sprintf('[AdherentMessage] Citizen project "%s" does not have mailchimp ID', $citizenProject->getUuidAsString()));
        }

        return $citizenProject->getMailchimpId();
    }
}
