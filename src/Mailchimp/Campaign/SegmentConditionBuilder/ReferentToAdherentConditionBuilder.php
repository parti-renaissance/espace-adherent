<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\StaticSegmentIdMissingException;

class ReferentToAdherentConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof ReferentUserFilter;
    }

    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        if (!$campaign->getStaticSegmentId()) {
            throw new StaticSegmentIdMissingException(\sprintf('[ReferentMessage] Referent message (%s) does not have a Mailchimp Static segment ID', $campaign->getMessage()->getUuid()->toString()));
        }

        return $campaign->getStaticSegmentId();
    }
}
