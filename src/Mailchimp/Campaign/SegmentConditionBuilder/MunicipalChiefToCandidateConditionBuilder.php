<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Mailchimp\Exception\InvalidFilterException;
use AppBundle\Mailchimp\Synchronisation\ApplicationRequestTagLabelEnum;
use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;

class MunicipalChiefToCandidateConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return
            $filter instanceof MunicipalChiefFilter
            && (
                $filter->getContactOnlyRunningMates()
                || $filter->getContactOnlyVolunteers()
                || $filter->getContactRunningMateTeam()
                || $filter->getContactVolunteerTeam()
            );
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var MunicipalChiefFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

        if (!$inseeCode = $filter->getInseeCode()) {
            throw new InvalidFilterException(
                $campaign->getMessage(),
                '[MunicipalChiefMessage] Message does not have a valid city value'
            );
        }

        $conditions[] = [
            'condition_type' => 'TextMerge',
            'op' => 'contains',
            'field' => MemberRequest::MERGE_FIELD_FAVORITE_CITIES_CODES,
            'value' => $this->formatCodeValue($inseeCode),
        ];

        if ($filter->getContactRunningMateTeam() || $filter->getContactVolunteerTeam()) {
            if (isset(FranceCitiesBundle::SPECIAL_CITY_ZONES[$inseeCode])) {
                $operator = [
                    'op' => 'starts',
                    'value' => rtrim($inseeCode, '0'),
                ];
            } else {
                $operator = [
                    'op' => 'is',
                    'value' => $inseeCode,
                ];
            }

            $conditions[] = array_merge(
                [
                    'condition_type' => 'TextMerge',
                    'field' => MemberRequest::MERGE_FIELD_MUNICIPAL_TEAM,
                ],
                $operator
            );

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
            if (isset(FranceCitiesBundle::SPECIAL_CITY_ZONES[$inseeCode])) {
                $operator = [
                    'op' => 'notcontain',
                    'value' => rtrim($inseeCode, '0'),
                ];
            } else {
                $operator = [
                    'op' => 'not',
                    'value' => $inseeCode,
                ];
            }

            $conditions[] = array_merge(
                [
                    'condition_type' => 'TextMerge',
                    'field' => MemberRequest::MERGE_FIELD_MUNICIPAL_TEAM,
                ],
                $operator
            );

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

    private function formatCodeValue(string $inseeCode): string
    {
        return '#'.(isset(FranceCitiesBundle::SPECIAL_CITY_ZONES[$inseeCode]) ? rtrim($inseeCode, '0') : $inseeCode);
    }
}
