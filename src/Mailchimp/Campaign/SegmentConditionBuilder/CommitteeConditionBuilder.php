<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Mailchimp\Exception\InvalidFilterException;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;

class CommitteeConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof CommitteeFilter;
    }

    /**
     * @var CommitteeFilter
     */
    protected function getSegmentId(AdherentMessageFilterInterface $filter): int
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
