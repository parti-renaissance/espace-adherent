<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Exception\InvalidFilterException;
use AppBundle\Mailchimp\Synchronisation\ApplicationRequestTagLabelEnum;
use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;

class MunicipalChiefToCandidateConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof MunicipalChiefFilter && false === $filter->getContactAdherents();
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var MunicipalChiefFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

        if (!$filter->getInseeCode()) {
            throw new InvalidFilterException(
                $campaign->getMessage(),
                '[MunicipalChiefMessage] Message does not have a valid city value'
            );
        }

        $conditions[] = [
            'condition_type' => 'TextMerge',
            'op' => 'contains',
            'field' => MemberRequest::MERGE_FIELD_FAVORITE_CITIES,
            'value' => $filter->getInseeCode(),
        ];

        if ($filter->getContactRunningMateTeam() || $filter->getContactVolunteerTeam()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => MemberRequest::MERGE_FIELD_MUNICIPAL_TEAM,
                'value' => $filter->getInseeCode(),
            ];

            if ($filter->getContactRunningMateTeam() ^ $filter->getContactVolunteerTeam()) {
                $conditions[] = $this->buildStaticSegmentCondition(
                    $this->mailchimpObjectIdMapping->getApplicationRequestTagIds()[
                    $filter->getContactRunningMateTeam()
                        ? ApplicationRequestTagLabelEnum::RUNNING_MATE
                        : ApplicationRequestTagLabelEnum::VOLUNTEER
                    ]
                );
            }
        } elseif ($filter->getContactOnlyRunningMates() || $filter->getContactOnlyVolunteers()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'not',
                'field' => MemberRequest::MERGE_FIELD_MUNICIPAL_TEAM,
                'value' => $filter->getInseeCode(),
            ];

            if ($filter->getContactOnlyRunningMates() ^ $filter->getContactOnlyVolunteers()) {
                $conditions[] = $this->buildStaticSegmentCondition(
                    $this->mailchimpObjectIdMapping->getApplicationRequestTagIds()[
                    $filter->getContactOnlyRunningMates()
                        ? ApplicationRequestTagLabelEnum::RUNNING_MATE
                        : ApplicationRequestTagLabelEnum::VOLUNTEER
                    ]
                );
            }
        }

        return $conditions;
    }
}
