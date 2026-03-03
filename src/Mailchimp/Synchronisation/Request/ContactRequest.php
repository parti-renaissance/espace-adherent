<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Request;

use App\Mailchimp\Contact\MarketingConsentStatusEnum;

class ContactRequest
{
    private MarketingConsentStatusEnum $emailConsent = MarketingConsentStatusEnum::UNKNOWN;
    private ?string $smsPhone = null;
    private bool $isSmsSubscribed = false;
    private array $mergeFields = [];

    public function __construct(private readonly string $email)
    {
    }

    public function setEmailConsent(MarketingConsentStatusEnum $emailConsent): void
    {
        $this->emailConsent = $emailConsent;
    }

    public function setSmsPhone(?string $smsPhone): void
    {
        $this->smsPhone = $smsPhone;
    }

    public function setSmsSubscribed(bool $isSmsSubscribed): void
    {
        $this->isSmsSubscribed = $isSmsSubscribed;
    }

    public function setMergeFields(array $mergeFields): void
    {
        $this->mergeFields = $mergeFields;
    }

    public function getPhone(): ?string
    {
        return $this->smsPhone;
    }

    public function toArray(): array
    {
        $data = [
            'language' => 'fr',
            'email_channel' => [
                'email' => $this->email,
                'marketing_consent' => [
                    'status' => $this->emailConsent->value,
                ],
            ],
        ];

        if ($this->smsPhone) {
            $data['sms_channel'] = [
                'sms_phone' => $this->smsPhone,
                'marketing_consent' => [
                    'status' => $this->isSmsSubscribed ? 'confirmed' : 'unknown',
                ],
            ];
        }

        if ($this->mergeFields) {
            $data['merge_fields'] = array_map(
                static fn ($value) => $value ?? '',
                $this->mergeFields
            );
        }

        return $data;
    }
}
