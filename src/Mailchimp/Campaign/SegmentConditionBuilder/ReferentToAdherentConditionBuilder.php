<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\ReferentTerritorialCouncilFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\StaticSegmentIdMissingException;

class ReferentToAdherentConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return
            ($filter instanceof ReferentUserFilter && false === $filter->getContactOnlyVolunteers() && false === $filter->getContactOnlyRunningMates())
            || ($filter instanceof ReferentTerritorialCouncilFilter)
        ;
    }

    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        if (!$campaign->getStaticSegmentId()) {
            throw new StaticSegmentIdMissingException(sprintf('[ReferentMessage] Referent message (%s) does not have a Mailchimp Static segment ID', $campaign->getMessage()->getUuid()->toString()));
        }

        return $campaign->getStaticSegmentId();
    }
}
