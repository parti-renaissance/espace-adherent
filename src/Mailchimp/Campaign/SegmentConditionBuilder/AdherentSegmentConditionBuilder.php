<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\AdherentSegmentAwareFilterInterface;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentSegment;
use AppBundle\Mailchimp\Exception\InvalidFilterException;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;

class AdherentSegmentConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof AdherentSegmentAwareFilterInterface && $filter->getAdherentSegment();
    }

    /**
     * @param AdherentSegmentAwareFilterInterface $filter
     */
    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        /** @var AdherentSegment $segment */
        if (!$segment = $filter->getAdherentSegment()) {
            throw new InvalidFilterException($filter->getMessage(), '[AdherentMessage] Adherent segment should not be empty');
        }

        if (!$segment->getMailchimpId()) {
            throw new StaticSegmentIdMissingException(sprintf('[AdherentMessage] AdherentSegment "%s" does not have mailchimp ID', $segment->getUuid()->toString()));
        }

        return $segment->getMailchimpId();
    }
}
