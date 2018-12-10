<?php

namespace AppBundle\Mailchimp\Synchronisation;

use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;
use AppBundle\Mailchimp\Synchronisation\Request\MemberTagsRequest;

class RequestBuilder
{
    private $email;
    private $gender;
    private $firstName;
    private $lastName;
    private $birthDay;
    private $city;
    private $zipCode;

    private $interests = [];

    private $activeTags = [];
    private $inactiveTags = [];

    private $committeeFollower = false;
    private $committeeHost = false;
    private $committeeSupervisor = false;

    private $citizenProjectHost = false;

    public static function createFromAdherent(Adherent $adherent, array $interestIds): self
    {
        return (new self())
            ->setGender($adherent->getGender())
            ->setFirstName($adherent->getFirstName())
            ->setLastName($adherent->getLastName())
            ->setBirthDay($adherent->getBirthdate())
            ->setZipCode($adherent->getPostalCode())
            ->setCity($adherent->getCityName())
            ->setInterests(
                array_replace(
                    array_fill_keys($interestIds, false),
                    array_fill_keys(
                        array_intersect_key($interestIds, array_flip($adherent->getInterests())),
                        true
                    )
                )
            )
            ->setActiveTags($adherent->getReferentTagCodes())
            ->setCommitteeFollower(
                !($memberships = $adherent->getMemberships())
                    ->getCommitteeFollowerMembershipsNotWaitingForApproval()
                    ->isEmpty()
            )
            ->setCommitteeHost(
                !$memberships
                    ->getCommitteeHostMemberships(CommitteeMembershipCollection::EXCLUDE_SUPERVISORS)
                    ->isEmpty()
            )
            ->setCommitteeSupervisor(!$memberships->getCommitteeSupervisorMemberships()->isEmpty())
            ->setCitizenProjectHost($adherent->isCitizenProjectAdministrator())
        ;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

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

    public function setBirthDay(?\DateTime $birthDay): self
    {
        $this->birthDay = $birthDay;

        return $this;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function setInterests(array $interests): self
    {
        $this->interests = $interests;

        return $this;
    }

    public function setActiveTags(array $activeTags): self
    {
        $this->activeTags = $activeTags;

        return $this;
    }

    public function setInactiveTags(array $inactiveTags): self
    {
        $this->inactiveTags = $inactiveTags;

        return $this;
    }

    public function setCommitteeFollower(bool $committeeFollower): self
    {
        $this->committeeFollower = $committeeFollower;

        return $this;
    }

    public function setCommitteeHost(bool $committeeHost): self
    {
        $this->committeeHost = $committeeHost;

        return $this;
    }

    public function setCommitteeSupervisor(bool $committeeSupervisor): self
    {
        $this->committeeSupervisor = $committeeSupervisor;

        return $this;
    }

    public function setCitizenProjectHost(bool $citizenProjectHost): self
    {
        $this->citizenProjectHost = $citizenProjectHost;

        return $this;
    }

    public function buildMemberRequest(string $memberIdentifier): MemberRequest
    {
        $request = new MemberRequest($memberIdentifier);

        if ($this->email) {
            $request->setEmailAddress($this->email);
        }

        $request->setMergeFields($this->buildMergeFields());

        if ($this->interests) {
            $request->setInterests($this->interests);
        }

        return $request;
    }

    public function createMemberTagsRequest(string $memberIdentifier, array $removedTags = []): MemberTagsRequest
    {
        $request = new MemberTagsRequest($memberIdentifier);

        foreach (array_merge($removedTags, $this->inactiveTags) as $tagName) {
            $request->addTag($tagName, false);
        }

        foreach ($this->activeTags as $tagName) {
            $request->addTag($tagName, true);
        }

        return $request;
    }

    private function buildMergeFields(): array
    {
        $mergeFields = [];

        if ($this->gender) {
            $mergeFields[MemberRequest::MERGE_FIELD_GENDER] = $this->gender;
        }

        if ($this->firstName) {
            $mergeFields[MemberRequest::MERGE_FIELD_FIRST_NAME] = $this->firstName;
        }

        if ($this->lastName) {
            $mergeFields[MemberRequest::MERGE_FIELD_LAST_NAME] = $this->lastName;
        }

        if ($this->birthDay) {
            $mergeFields[MemberRequest::MERGE_FIELD_BIRTHDATE] = $this->birthDay->format('Y-m-d');
        }

        if ($this->city) {
            $mergeFields[MemberRequest::MERGE_FIELD_CITY] = $this->city;
        }

        if ($this->zipCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_ZIP_CODE] = $this->zipCode;
        }

        if ($this->activeTags) {
            $mergeFields[MemberRequest::MERGE_FIELD_TAGS] = implode(',', $this->activeTags);
        }

        $mergeFields[MemberRequest::MERGE_FIELD_COMMITTEE_FOLLOWER] = (int) $this->committeeFollower;
        $mergeFields[MemberRequest::MERGE_FIELD_COMMITTEE_SUPERVISOR] = (int) $this->committeeSupervisor;
        $mergeFields[MemberRequest::MERGE_FIELD_COMMITTEE_HOST] = (int) $this->committeeHost;
        $mergeFields[MemberRequest::MERGE_FIELD_CITIZEN_PROJECT_HOST] = (int) $this->citizenProjectHost;

        return $mergeFields;
    }
}
