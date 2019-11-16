<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Exception\InvalidFilterException;
use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;
use AppBundle\Newsletter\NewsletterTypeEnum;

class MunicipalChiefToNewsletterConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof MunicipalChiefFilter && $filter->getContactNewsletter();
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var MunicipalChiefFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

        if (!$filter->getInseeCode()) {
            throw new InvalidFilterException($campaign->getMessage(), '[MunicipalChiefMessage] Message does not have a valid city value');
        }

        $conditions[] = [
            'condition_type' => 'TextMerge',
            'op' => 'contains',
            'field' => MemberRequest::MERGE_FIELD_INSEE_CODE,
            'value' => $filter->getInseeCode(),
        ];

        $conditions[] = $this->buildStaticSegmentCondition(
            $this->mailchimpObjectIdMapping->getNewsletterTagIds()[NewsletterTypeEnum::SITE_MUNICIPAL]
        );

        return $conditions;
    }
}
