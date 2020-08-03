<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\MailchimpSegment;

class ReferentToElectedRepresentativeConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof ReferentElectedRepresentativeFilter;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        return array_map(function (MailchimpSegment $mailchimpSegment) {
            return $mailchimpSegment->getExternalId();
        }, $campaign->getMailchimpSegments()->toArray());
    }
}
