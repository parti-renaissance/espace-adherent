<?php

namespace AppBundle\Mailchimp\Synchronisation\Request;

class MemberRequest implements MemberRequestInterface
{
    public const MERGE_FIELD_FIRST_NAME = 'MERGE1';
    public const MERGE_FIELD_LAST_NAME = 'MERGE2';
    public const MERGE_FIELD_CITY = 'MERGE3';
    public const MERGE_FIELD_GENDER = 'MERGE4';
    public const MERGE_FIELD_BIRTHDATE = 'MERGE5';
    public const MERGE_FIELD_ZIP_CODE = 'ZIP_CODE';
    public const MERGE_FIELD_TAGS = 'TAGS';

    private $memberIdentifier;

    private $emailAddress;
    private $emailType = 'html'; // or 'text'
    private $status = 'subscribed';
    private $mergeFields = [];
    private $interests = [];

    public function __construct(string $memberIdentifier)
    {
        $this->memberIdentifier = $memberIdentifier;
    }

    public function getMemberIdentifier(): string
    {
        return $this->memberIdentifier;
    }

    public function setEmailAddress($emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function setUnsubscriptionRequest(): void
    {
        $this->status = 'unsubscribed';
    }

    public function setMergeFields(array $mergeFields): void
    {
        $this->mergeFields = $mergeFields;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function toArray(): array
    {
        return [
            'email_address' => $this->emailAddress ?? $this->memberIdentifier,
            'email_type' => $this->emailType,
            'status' => $this->status,
            'merge_fields' => $this->mergeFields,
            'interests' => $this->interests,
        ];
    }
}
