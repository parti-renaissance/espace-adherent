<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignRequest;
use AppBundle\Mailchimp\Manager;
use AppBundle\Utils\StringCleaner;

class CampaignRequestBuilder
{
    private $objectIdMapping;
    private $listId;
    private $replyEmailAddress;
    private $fromName;
    private $memberGroupInterestGroupId;
    private $interestIds;
    private $memberInterestInterestGroupId;

    public function __construct(
        MailchimpObjectIdMapping $objectIdMapping,
        string $listId,
        array $interestIds,
        string $memberGroupInterestGroupId,
        string $memberInterestInterestGroupId,
        string $replyEmailAddress,
        string $fromName
    ) {
        $this->objectIdMapping = $objectIdMapping;
        $this->listId = $listId;
        $this->interestIds = $interestIds;
        $this->memberGroupInterestGroupId = $memberGroupInterestGroupId;
        $this->memberInterestInterestGroupId = $memberInterestInterestGroupId;
        $this->replyEmailAddress = $replyEmailAddress;
        $this->fromName = $fromName;
    }

    public function createEditCampaignRequestFromMessage(AdherentMessageInterface $message): EditCampaignRequest
    {
        return (new EditCampaignRequest($this->listId))
            ->setFolderId($this->objectIdMapping->getFolderIdByType($message->getType()))
            ->setTemplateId($this->objectIdMapping->getTemplateIdByType($message->getType()))
            ->setSubject($message->getSubject())
            ->setTitle(sprintf('%s - %s', $message->getAuthor(), (new \DateTime())->format('d/m/Y')))
            ->setSegmentOptions($message->getFilter() ? $this->buildSegmentOptions($message->getFilter()) : [])
            ->setFromName($message->getFromName() ?? $this->fromName)
            ->setReplyTo($message->getReplyTo() ?? $this->replyEmailAddress)
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

    private function buildSegmentOptions(AdherentMessageFilterInterface $filter): array
    {
        if ($filter instanceof ReferentUserFilter) {
            $conditions = $this->buildReferentConditions($filter);
        } elseif ($filter instanceof AdherentZoneFilter) {
            $conditions = $this->buildReferentZoneConditions($filter->getReferentTags());
            $match = 'any';
        }

        return [
            'match' => $match ?? 'all',
            'conditions' => $conditions ?? [],
        ];
    }

    private function buildReferentZoneConditions(array $referentTags): array
    {
        $conditions = [];

        foreach ($referentTags as $tag) {
            $conditions[] = [
                'condition_type' => 'StaticSegment',
                'op' => 'static_is',
                'field' => 'static_segment',
                'value' => $tag->getExternalId(),
            ];
        }

        return $conditions;
    }

    private function buildReferentConditions(ReferentUserFilter $filter): array
    {
        $conditions = [];

        if (
            $filter->includeCitizenProjectHosts()
            || $filter->includeCommitteeHosts()
            || $filter->includeCommitteeSupervisors()
            || $filter->includeAdherentsInCommittee()
            || $filter->includeAdherentsNoCommittee()
        ) {
            $interestKeys = [];

            if ($filter->includeCitizenProjectHosts()) {
                $interestKeys[] = Manager::INTEREST_KEY_CP_HOST;
            }

            if ($filter->includeCommitteeSupervisors()) {
                $interestKeys[] = Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR;
            }

            if ($filter->includeCommitteeHosts()) {
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

        if ($filter->getGender()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'GENDER',
                'value' => $filter->getGender(),
            ];
        }

        $now = new \DateTimeImmutable('now');

        if ($minAge = $filter->getAgeMin()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => 'BIRTHDATE',
                'value' => $now->modify(sprintf('-%d years', $minAge))->format('Y-m-d'),
            ];
        }

        if ($maxAge = $filter->getAgeMax()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => 'BIRTHDATE',
                'value' => $now->modify(sprintf('-%d years', $maxAge))->format('Y-m-d'),
            ];
        }

        if ($filter->getFirstName()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'FIRST_NAME',
                'value' => $filter->getFirstName(),
            ];
        }

        if ($filter->getLastName()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => 'LAST_NAME',
                'value' => $filter->getLastName(),
            ];
        }

        if ($filter->getCity()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'field' => 'CITY',
                'value' => $filter->getCity(),
            ];
        }

        if ($filter->getInterests()) {
            $conditions[] = [
                'condition_type' => 'Interests',
                'op' => 'interestcontainsall',
                'field' => sprintf('interests-%s', $this->memberInterestInterestGroupId),
                'value' => array_values(
                    array_intersect_key($this->interestIds, array_fill_keys($filter->getInterests(), true))
                ),
            ];
        }

        return array_merge(
            $conditions,
            $this->buildReferentZoneConditions([$filter->getReferentTag()])
        );
    }
}
