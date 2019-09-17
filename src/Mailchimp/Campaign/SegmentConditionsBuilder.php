<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\SegmentConditionBuilderInterface;

class SegmentConditionsBuilder
{
    private $mailchimpObjectIdMapping;
    /** @var SegmentConditionBuilderInterface[] */
    private $builders;

    public function __construct(MailchimpObjectIdMapping $mailchimpObjectIdMapping, iterable $builders)
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
        $this->builders = $builders;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        $message = $campaign->getMessage();
        $filter = $message->getFilter();

        if (!$filter) {
            throw new \InvalidArgumentException('Filter is null');
        }

        $conditions = [];

        foreach ($this->builders as $builder) {
            if ($builder->support($filter)) {
                $conditions = array_merge($conditions, $builder->build($campaign));
                $built = true;
            }
        }

        if (!isset($built)) {
            throw new \RuntimeException(sprintf('Any builder was found for the filter class: %s', \get_class($filter)));
        }

        return [
            'list_id' => $this->getListId($message),
            'segment_opts' => [
                'match' => 'all',
                'conditions' => $conditions ?? [],
            ],
        ];
    }

    private function getListId(AdherentMessageInterface $message): string
    {
        if ($filter = $message->getFilter()) {
            if ($filter instanceof MunicipalChiefFilter && ($filter->getContactAdherents() || $filter->getContactNewsletter())) {
                if ($filter->getContactAdherents()) {
                    return $this->mailchimpObjectIdMapping->getMainListId();
                }

                return $this->mailchimpObjectIdMapping->getNewsletterListId();
            }

            if ($filter instanceof ReferentUserFilter && ($filter->getContactOnlyRunningMates() || $filter->getContactOnlyVolunteers())) {
                return $this->mailchimpObjectIdMapping->getApplicationRequestCandidateListId();
            }
        }

        return $this->mailchimpObjectIdMapping->getListIdByMessageType($message->getType());
    }
}
