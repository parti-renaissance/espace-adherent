<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Filter\FilterDataObjectInterface;
use AppBundle\AdherentMessage\Filter\ReferentFilterDataObject;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignRequest;
use AppBundle\Mailchimp\Manager;
use AppBundle\Utils\StringCleaner;

class CampaignRequestBuilder
{
    private $objectIdMapping;
    private $listId;
    private $interestIds;
    private $memberGroupInterestGroupId;
    private $memberInterestInterestGroupId;

    public function __construct(
        MailchimpObjectIdMapping $objectIdMapping,
        string $listId,
        array $interestIds,
        string $memberGroupInterestGroupId,
        string $memberInterestInterestGroupId
    ) {
        $this->objectIdMapping = $objectIdMapping;
        $this->listId = $listId;
        $this->interestIds = $interestIds;
        $this->memberGroupInterestGroupId = $memberGroupInterestGroupId;
        $this->memberInterestInterestGroupId = $memberInterestInterestGroupId;
    }

    public function createEditCampaignRequestFromMessage(AdherentMessageInterface $message): EditCampaignRequest
    {
        return (new EditCampaignRequest($this->listId))
            ->setFolderId($this->objectIdMapping->getFolderIdByType($message->getType()))
            ->setTemplateId($this->objectIdMapping->getTemplateIdByType($message->getType()))
            ->setSubject($message->getSubject())
            ->setTitle(sprintf('%s - %s', $message->getAuthor(), (new \DateTime())->format('d/m/Y')))
            ->setConditions($this->buildFilterConditions($message->getFilter()))
        ;
    }

    public function createContentRequest(AdherentMessageInterface $message): EditCampaignContentRequest
    {
        $request = new EditCampaignContentRequest(
            $this->objectIdMapping->getTemplateIdByType($message->getType()),
            $message->getContent()
        );

        switch ($message->getType()) {
            case AdherentMessageTypeEnum::DEPUTY:
                $request
                    ->addSection('full_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFullName()))
                    ->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()))
                    ->addSection('district_name', (string) $message->getAuthor()->getManagedDistrict())
                ;
                break;

            case AdherentMessageTypeEnum::REFERENT:
                $request->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()));
                break;
        }

        return $request;
    }

    private function buildFilterConditions(?FilterDataObjectInterface $filter): array
    {
        if ($filter instanceof ReferentFilterDataObject) {
            return $this->buildReferentConditions($filter);
        }

        return [];
    }

    private function buildReferentConditions(ReferentFilterDataObject $filter): array
    {
        $conditions = [];

        if (
            $filter->includeCitizenProject()
            || $filter->includeHosts()
            || $filter->includeSupervisors()
            || $filter->includeAdherentsInCommittee()
            || $filter->includeAdherentsNoCommittee()
        ) {
            $interestKeys = [];

            if ($filter->includeCitizenProject()) {
                $interestKeys[] = Manager::INTEREST_KEY_CP_HOST;
            }

            if ($filter->includeSupervisors()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR;
            }

            if ($filter->includeHosts()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_HOST;
            }

            if ($filter->includeAdherentsInCommittee()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_FOLLOWER;
            }

            if ($filter->includeAdherentsNoCommittee()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_NO_FOLLOWER;
            }

            $conditions[] = [
                'condition_type' => 'Interests',
                'op' => 'interestcontains',
                'field' => sprintf('interests-%s', $this->memberGroupInterestGroupId),
                'value' => array_values(
                    array_intersect_key($this->interestIds, array_fill_keys($interestKeys, true))
                ),
            ];
        }

        if ($filter->getQueryGender()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'GENDER',
                'value' => $filter->getQueryGender(),
            ];
        }

        $now = new \DateTimeImmutable('now');

        if ($minAge = $filter->getQueryAgeMinimum()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => 'BIRTHDATE',
                'value' => $now->modify(sprintf('-%d years', $minAge))->format('Y-m-d'),
            ];
        }

        if ($maxAge = $filter->getQueryAgeMaximum()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => 'BIRTHDATE',
                'value' => $now->modify(sprintf('-%d years', $maxAge))->format('Y-m-d'),
            ];
        }

        if ($filter->getQueryFirstName()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'FIRST_NAME',
                'value' => $filter->getQueryFirstName(),
            ];
        }

        if ($filter->getQueryLastName()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'LAST_NAME',
                'value' => $filter->getQueryLastName(),
            ];
        }

        if ($filter->getQueryCity()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'CITY',
                'value' => $filter->getQueryCity(),
            ];
        }

        if ($filter->getQueryAreaCode()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'starts',
                'field' => 'ZIP_CODE',
                'value' => $filter->getQueryAreaCode(),
            ];
        }

        if ($filter->getQueryInterests()) {
            $conditions[] = [
                'condition_type' => 'Interests',
                'op' => 'interestcontainsall',
                'field' => sprintf('interests-%s', $this->memberInterestInterestGroupId),
                'value' => array_values(
                    array_intersect_key($this->interestIds, array_fill_keys($filter->getQueryInterests(), true))
                ),
            ];
        }

        return $conditions;
    }
}
