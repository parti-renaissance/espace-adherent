<?php

namespace App\Mailchimp\Synchronisation\MemberRequest;

use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use App\Newsletter\NewsletterTypeEnum;
use App\Newsletter\NewsletterValueObject;

class NewsletterMemberRequestBuilder extends AbstractMemberRequestBuilder
{
    private $zipCode;
    private $countryName;
    private $type;
    private $siteCode;

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

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
            ->setZipCode($newsletter->getZipCode())
            ->setCountryName($newsletter->getCountryName())
            ->setType($newsletter->getType())
            ->setSiteCode($newsletter->getSiteCode())
            ->setIsSubscribeRequest($newsletter->isSubscribed())
        ;
    }

    protected function buildMergeFields(): array
    {
        $mergeFields = [];

        if ($this->zipCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_ZIP_CODE] = $this->zipCode;
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
