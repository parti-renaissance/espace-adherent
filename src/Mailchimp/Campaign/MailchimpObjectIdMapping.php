<?php

namespace AppBundle\Mailchimp\Campaign;

class MailchimpObjectIdMapping
{
    private $folderIds;
    private $templateIds;
    private $interestIds;
    private $memberGroupInterestGroupId;
    private $memberInterestInterestGroupId;
    private $subscriptionTypeInterestGroupId;

    public function __construct(
        array $folderIds,
        array $templateIds,
        array $interestIds,
        string $memberGroupInterestGroupId,
        string $memberInterestInterestGroupId,
        string $subscriptionTypeInterestGroupId
    ) {
        $this->folderIds = $folderIds;
        $this->templateIds = $templateIds;
        $this->interestIds = $interestIds;
        $this->memberGroupInterestGroupId = $memberGroupInterestGroupId;
        $this->memberInterestInterestGroupId = $memberInterestInterestGroupId;
        $this->subscriptionTypeInterestGroupId = $subscriptionTypeInterestGroupId;
    }

    public function getFolderIdByType(string $messageType): ?string
    {
        return $this->folderIds[$messageType] ?? null;
    }

    public function getTemplateIdByType(string $messageType): ?int
    {
        return $this->templateIds[$messageType] ?? null;
    }

    public function getInterestIds(): array
    {
        return $this->interestIds;
    }

    public function getMemberGroupInterestGroupId(): string
    {
        return $this->memberGroupInterestGroupId;
    }

    public function getMemberInterestInterestGroupId(): string
    {
        return $this->memberInterestInterestGroupId;
    }

    public function getSubscriptionTypeInterestGroupId(): string
    {
        return $this->subscriptionTypeInterestGroupId;
    }
}
