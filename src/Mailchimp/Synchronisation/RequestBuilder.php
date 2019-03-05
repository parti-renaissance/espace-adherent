<?php

namespace AppBundle\Mailchimp\Synchronisation;

use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\SubscriptionType;
use AppBundle\Mailchimp\Manager;
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

    private $mailchimpInterestIds = [];

    public function __construct(array $mailchimpInterestIds = [])
    {
        $this->mailchimpInterestIds = $mailchimpInterestIds;
    }

    public function updateFromAdherent(Adherent $adherent): self
    {
        return $this
            ->setEmail($adherent->getEmailAddress())
            ->setGender($adherent->getGender())
            ->setFirstName($adherent->getFirstName())
            ->setLastName($adherent->getLastName())
            ->setBirthDay($adherent->getBirthdate())
            ->setZipCode($adherent->getPostalCode())
            ->setCity($adherent->getCityName())
            ->setInterests($this->buildInterestArray($adherent))
            ->setActiveTags($adherent->getReferentTagCodes())
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
            $mergeFields[MemberRequest::MERGE_FIELD_CITY] = sprintf('%s (%s)', $this->city, $this->zipCode);
        }

        if ($this->zipCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_ZIP_CODE] = $this->zipCode;
        }

        if ($this->activeTags) {
            $mergeFields[MemberRequest::MERGE_FIELD_TAGS] = implode(',', $this->activeTags);
        }

        return $mergeFields;
    }

    private function buildInterestArray(Adherent $adherent): array
    {
        return array_replace(
            // By default all interests are disabled (`false` value) for a member
            array_fill_keys($this->mailchimpInterestIds, false),

            // Activate adherent's interests
            array_fill_keys(
                array_intersect_key(
                    $this->mailchimpInterestIds,
                    array_flip($adherent->getInterests())
                ),
                true
            ),

            /*
             * Activate Notification group interests.
             *
             * This is a hack to migrate progressively the ID stored
             * into DB (subscription_types.external_id column), after that we will be able to use this method:
             * array_fill_keys(array_intersect($this->mailchimpInterestIds, $adherent->getSubscriptionExternalIds()), true),
             */
            array_fill_keys(
                array_intersect_key(
                    $this->mailchimpInterestIds,
                    array_flip(
                        array_map(
                            function (SubscriptionType $type) { return $type->getCode(); },
                            $adherent->getSubscriptionTypes()
                        )
                    )
                ),
                true
            ),

            // Activate Member group interest
            array_fill_keys(
                array_intersect_key(
                    $this->mailchimpInterestIds,
                    array_filter([
                        Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR => !($memberships = $adherent->getMemberships())->getCommitteeSupervisorMemberships()->isEmpty(),
                        Manager::INTEREST_KEY_COMMITTEE_HOST => !$memberships->getCommitteeHostMemberships(CommitteeMembershipCollection::EXCLUDE_SUPERVISORS)->isEmpty(),
                        Manager::INTEREST_KEY_COMMITTEE_FOLLOWER => $isFollower = !$memberships->getCommitteeFollowerMembershipsNotWaitingForApproval()->isEmpty(),
                        Manager::INTEREST_KEY_COMMITTEE_NO_FOLLOWER => !$isFollower,
                        Manager::INTEREST_KEY_CP_HOST => $adherent->isCitizenProjectAdministrator(),
                    ])
                ),
                true
            )
        );
    }
}
