<?php

namespace App\Mailchimp\Synchronisation\MemberRequest;

use App\Entity\Geo\Zone;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use App\Newsletter\NewsletterTypeEnum;
use App\Newsletter\NewsletterValueObject;

class NewsletterMemberRequestBuilder extends AbstractMemberRequestBuilder
{
    private $zipCode;
    private $firstName;
    private $lastName;
    private $countryName;
    private $type;
    private $siteCode;
    private array $zones = [];

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setZones(?array $zones): self
    {
        $this->zones = $zones;

        return $this;
    }

    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setSiteCode(?string $siteCode): self
    {
        $this->siteCode = $siteCode;

        return $this;
    }

    public function updateFromValueObject(NewsletterValueObject $newsletter): self
    {
        return $this
            ->setEmail($newsletter->getEmail())
            ->setFirstName($newsletter->getFirstName())
            ->setLastName($newsletter->getLastName())
            ->setZipCode($newsletter->getZipCode())
            ->setCountryName($newsletter->getCountryName())
            ->setType($newsletter->getType())
            ->setZones($newsletter->getZones())
            ->setIsSubscribeRequest($newsletter->isSubscribed())
        ;
    }

    protected function buildMergeFields(): array
    {
        $mergeFields = [];

        if ($this->zipCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_ZIP_CODE] = $this->zipCode;
        }

        if ($this->firstName) {
            $mergeFields[MemberRequest::MERGE_FIELD_FIRST_NAME] = $this->firstName;
        }

        if ($this->lastName) {
            $mergeFields[MemberRequest::MERGE_FIELD_LAST_NAME] = $this->lastName;
        }

        if ($this->countryName) {
            $mergeFields[MemberRequest::MERGE_FIELD_COUNTRY] = $this->countryName;
        }

        switch ($this->type) {
            case NewsletterTypeEnum::SITE_DEPARTMENTAL:
                $mergeFields[MemberRequest::MERGE_FIELD_DEPARTMENTAL_CODE] = $this->siteCode;
                break;

            case NewsletterTypeEnum::SITE_MUNICIPAL:
                $mergeFields[MemberRequest::MERGE_FIELD_INSEE_CODE] = $this->siteCode;
                break;

            default:
                $mergeFields[MemberRequest::MERGE_FIELD_SOURCE] = $this->type;
                break;
        }

        if ($this->zones) {
            $mergeFields[MemberRequest::MERGE_FIELD_ZONE_CODES] = implode(',', array_map(function (Zone $zone) {
                return $zone->getTypeCode();
            }, $this->zones));
        }

        return $mergeFields;
    }

    public function createMemberTagsRequest(string $memberIdentifier): MemberTagsRequest
    {
        $request = new MemberTagsRequest($memberIdentifier);

        switch ($this->type) {
            case NewsletterTypeEnum::MAIN_SITE:
                $request->addTag('EM!');
                break;

            case NewsletterTypeEnum::SITE_NRP:
                $request->addTag('Nouvelle République');
                break;

            case NewsletterTypeEnum::MAIN_SITE_FROM_EVENT:
                $request->addTag('EM!');
                $request->addTag('Ajouté depuis un événement');
                break;

            case NewsletterTypeEnum::SITE_DEPARTMENTAL:
                $request->addTag('Site départemental');
                break;

            case NewsletterTypeEnum::SITE_MUNICIPAL:
                $request->addTag('Site municipal');
                break;
        }

        return $request;
    }
}
