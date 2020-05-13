<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\InvalidFilterException;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Newsletter\NewsletterTypeEnum;

class MunicipalChiefToNewsletterConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof MunicipalChiefFilter && $filter->getContactNewsletter();
    }

    public function build(MailchimpCampaign $campaign): array
    {
        if (!$inseeCode = $campaign->getCity()) {
            throw new InvalidFilterException($campaign->getMessage(), '[MunicipalChiefMessage] Message does not have a valid city value');
        }

        $conditions[] = [
            'condition_type' => 'TextMerge',
            'op' => 'contains',
            'field' => MemberRequest::MERGE_FIELD_INSEE_CODE,
            'value' => $inseeCode,
        ];

        $conditions[] = $this->buildStaticSegmentCondition(
            $this->mailchimpObjectIdMapping->getNewsletterTagIds()[NewsletterTypeEnum::SITE_MUNICIPAL]
        );

        return $conditions;
    }
}
