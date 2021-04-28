<?php

namespace App\Mailchimp\Synchronisation\MemberRequest;

use App\Coalition\CoalitionContactValueObject;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class CoalitionMemberRequestBuilder extends AbstractMemberRequestBuilder
{
    private $firstName;
    private $lastName;
    private $gender;
    private $city;
    private $source;

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

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function updateFromValueObject(CoalitionContactValueObject $contact): self
    {
        return $this
            ->setEmail($contact->getEmail())
            ->setFirstName($contact->getFirstName())
            ->setLastName($contact->getLastName())
            ->setGender($contact->getGender())
            ->setCity($contact->getCity())
            ->setSource($contact->getSource())
        ;
    }

    protected function buildMergeFields(): array
    {
        $mergeFields = [];

        if ($this->firstName) {
            $mergeFields[MemberRequest::MERGE_FIELD_FIRST_NAME] = $this->firstName;
        }

        if ($this->lastName) {
            $mergeFields[MemberRequest::MERGE_FIELD_LAST_NAME] = $this->lastName;
        }

        if ($this->gender) {
            $mergeFields[MemberRequest::MERGE_FIELD_GENDER] = $this->gender;
        }

        if ($this->city) {
            $mergeFields[MemberRequest::MERGE_FIELD_CITY] = $this->city;
        }

        if ($this->source) {
            $mergeFields[MemberRequest::MERGE_FIELD_SOURCE] = $this->source;
        }

        return $mergeFields;
    }
}
