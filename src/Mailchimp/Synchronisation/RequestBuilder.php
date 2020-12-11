<?php

namespace App\Mailchimp\Synchronisation;

use App\Collection\CommitteeMembershipCollection;
use App\Entity\Adherent;
use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Entity\ApplicationRequest\VolunteerRequest;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Entity\SubscriptionType;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\MailchimpSegment\MailchimpSegmentTagEnum;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use App\Repository\ReferentTagRepository;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class RequestBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $email;
    private $gender;
    private $firstName;
    private $lastName;
    /** @var \DateTimeInterface */
    private $birthDay;
    private $city;
    private $zipCode;
    private $countryName;
    private $adhesionDate;
    /** @var bool|null */
    private $isAdherent;

    private $interests;

    private $activeTags = [];
    private $inactiveTags = [];
    private $favoriteCities;
    private $favoriteCitiesCodes;
    private $takenForCity = false;
    private $mailchimpObjectIdMapping;
    private $electedRepresentativeTagsBuilder;
    private $isSubscribeRequest = true;
    private $referentTagsCodes = [];

    /** @var Zone|null */
    private $zoneCity;
    private $zoneDepartment;
    private $zoneRegion;
    private $zoneCountry;

    private $teamCode;

    public function __construct(
        MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        ElectedRepresentativeTagsBuilder $electedRepresentativeTagsBuilder
    ) {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
        $this->electedRepresentativeTagsBuilder = $electedRepresentativeTagsBuilder;
        $this->logger = new NullLogger();
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
            ->setCountryName($adherent->getCountryName())
            ->setAdhesionDate($adherent->getRegisteredAt())
            ->setInterests($this->buildInterestArray($adherent))
            ->setActiveTags($this->getAdherentActiveTags($adherent))
            ->setInactiveTags($this->getInactiveTags($adherent))
            ->setIsSubscribeRequest($adherent->isEnabled() && false === $adherent->isEmailUnsubscribed())
            ->setZones($adherent->getZones())
            ->setTeamCode($adherent)
        ;
    }

    public function updateFromElectedRepresentative(ElectedRepresentative $electedRepresentative): self
    {
        return $this
            ->setEmail($electedRepresentative->getEmailAddress())
            ->setGender($electedRepresentative->getGender())
            ->setFirstName($electedRepresentative->getFirstName())
            ->setLastName($electedRepresentative->getLastName())
            ->setBirthDay($electedRepresentative->getBirthDate())
            ->setIsAdherent($electedRepresentative->isAdherent())
            ->setActiveTags($this->electedRepresentativeTagsBuilder->buildTags($electedRepresentative))
            ->setIsSubscribeRequest(false === $electedRepresentative->isEmailUnsubscribed())
        ;
    }

    public function updateFromApplicationRequest(ApplicationRequest $applicationRequest): self
    {
        $activeTags = $applicationRequest instanceof VolunteerRequest ?
            [ApplicationRequestTagLabelEnum::VOLUNTEER_LABEL]
            : [ApplicationRequestTagLabelEnum::RUNNING_MATE_LABEL];

        if ($applicationRequest->isAdherent()) {
            $activeTags[] = ApplicationRequestTagLabelEnum::ADHERENT_LABEL;
        }

        return $this
            ->setEmail($applicationRequest->getEmailAddress())
            ->setGender($applicationRequest->getGender())
            ->setFirstName($applicationRequest->getFirstName())
            ->setLastName($applicationRequest->getLastName())
            ->setFavoriteCities($applicationRequest->getFavoriteCitiesNames())
            ->setFavoriteCitiesCodes($applicationRequest->getFavoriteCityPrefixedCodes())
            ->setReferentTagCodes($applicationRequest->getReferentTagsCodes())
            ->setTakenForCity($applicationRequest->getTakenForCity())
            ->setActiveTags($activeTags)
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

    public function setBirthDay(?\DateTimeInterface $birthDay): self
    {
        $this->birthDay = $birthDay;

        return $this;
    }

    public function setIsAdherent(bool $isAdherent = null): self
    {
        $this->isAdherent = $isAdherent;

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

    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function setAdhesionDate(?\DateTimeInterface $adhesionDate): self
    {
        $this->adhesionDate = $adhesionDate;

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

    public function setIsSubscribeRequest(bool $isSubscribeRequest): self
    {
        $this->isSubscribeRequest = $isSubscribeRequest;

        return $this;
    }

    public function setFavoriteCities(array $favoriteCities): self
    {
        $this->favoriteCities = $favoriteCities;

        return $this;
    }

    public function setFavoriteCitiesCodes(array $favoriteCitiesCodes): self
    {
        $this->favoriteCitiesCodes = $favoriteCitiesCodes;

        return $this;
    }

    public function setReferentTagCodes(array $codes): self
    {
        $this->referentTagsCodes = $codes;

        return $this;
    }

    public function setTakenForCity(?string $takenForCity): self
    {
        $this->takenForCity = $takenForCity;

        return $this;
    }

    /**
     * @param Collection|Zone[] $zones
     */
    public function setZones(Collection $zones): self
    {
        foreach ($zones as $zone) {
            $this->setZone($zone);

            foreach ($zone->getParents() as $parent) {
                $this->setZone($parent);
            }
        }

        return $this;
    }

    private function setZone(Zone $zone): void
    {
        switch ($zone->getType()) {
            case Zone::CITY:
                $this->zoneCity = $zone;

                break;
            case Zone::DEPARTMENT:
                $this->zoneDepartment = $zone;

                break;
            case Zone::REGION:
                $this->zoneRegion = $zone;

                break;
            case Zone::COUNTRY:
                $this->zoneCountry = $zone;

                break;
            default:
                break;
        }
    }

    public function setTeamCode(Adherent $adherent): self
    {
        if ($adherent->isParisResident()) {
            $zones = $adherent->getZonesOfType(Zone::BOROUGH);
        } else {
            $zones = $adherent->getParentZonesOfType($adherent->isForeignResident() ? Zone::FOREIGN_DISTRICT : Zone::DEPARTMENT);
        }

        $count = \count($zones);
        if ($count > 1) {
            $this->logger->warning(\sprintf('Cannot find only one geo zone for Mailchimp for adherent with id "%s"', $adherent->getId()));
        }
        $this->teamCode = (1 === $count) ? current($zones)->getTeamCode() : null;

        return $this;
    }

    public function buildMemberRequest(string $memberIdentifier): MemberRequest
    {
        $request = new MemberRequest($memberIdentifier);

        if ($this->email) {
            $request->setEmailAddress($this->email);
        }

        if (false === $this->isSubscribeRequest) {
            $request->setUnsubscriptionRequest();
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
            $request->addTag($tagName);
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
            $mergeFields[MemberRequest::MERGE_FIELD_BIRTHDATE] = $this->birthDay->format(MemberRequest::DATE_FORMAT);
        }

        if (null !== $this->isAdherent) {
            $mergeFields[MemberRequest::MERGE_FIELD_ADHERENT] = $this->isAdherent ? 'oui' : 'non';
        }

        if ($this->city) {
            $mergeFields[MemberRequest::MERGE_FIELD_CITY] = sprintf('%s (%s)', $this->city, $this->zipCode);
        }

        if ($this->zipCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_ZIP_CODE] = $this->zipCode;
        }

        if ($this->countryName) {
            $mergeFields[MemberRequest::MERGE_FIELD_COUNTRY] = $this->countryName;
        }

        if ($this->adhesionDate) {
            $mergeFields[MemberRequest::MERGE_FIELD_ADHESION_DATE] = $this->adhesionDate->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->favoriteCities) {
            $mergeFields[MemberRequest::MERGE_FIELD_FAVORITE_CITIES] = implode(',', $this->favoriteCities);
            $mergeFields[MemberRequest::MERGE_FIELD_FAVORITE_CITIES_CODES] = implode(',', $this->favoriteCitiesCodes);
        }

        if ($this->referentTagsCodes) {
            $mergeFields[MemberRequest::MERGE_FIELD_REFERENT_TAGS] = implode(',', $this->referentTagsCodes);
        }

        if (false !== $this->takenForCity) {
            $mergeFields[MemberRequest::MERGE_FIELD_MUNICIPAL_TEAM] = (string) $this->takenForCity;
        }

        if ($this->zoneCity) {
            $mergeFields[MemberRequest::getMergeFieldFromZone($this->zoneCity)] = (string) $this->zoneCity;
        }

        if ($this->zoneDepartment) {
            $mergeFields[MemberRequest::getMergeFieldFromZone($this->zoneDepartment)] = (string) $this->zoneDepartment;
        }

        if ($this->zoneRegion) {
            $mergeFields[MemberRequest::getMergeFieldFromZone($this->zoneRegion)] = (string) $this->zoneRegion;
        }

        if ($this->zoneCountry) {
            $mergeFields[MemberRequest::getMergeFieldFromZone($this->zoneCountry)] = (string) $this->zoneCountry;
        }

        if ($this->teamCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_TEAM_CODE] = (string) $this->teamCode;
        }

        return $mergeFields;
    }

    private function buildInterestArray(Adherent $adherent): array
    {
        return array_replace(
            // By default all interests are disabled (`false` value) for a member
            array_fill_keys($ids = $this->mailchimpObjectIdMapping->getInterestIds(), false),

            // Activate adherent's interests
            array_fill_keys(
                array_intersect_key(
                    $ids,
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
                    $ids,
                    array_flip(
                        array_map(
                            static function (SubscriptionType $type) { return $type->getCode(); },
                            $adherent->getSubscriptionTypes()
                        )
                    )
                ),
                true
            ),

            // Activate Member group interest
            array_fill_keys(
                array_intersect_key(
                    $ids,
                    array_filter([
                        Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR => !($memberships = $adherent->getMemberships())->getCommitteeSupervisorMemberships()->isEmpty(),
                        Manager::INTEREST_KEY_COMMITTEE_HOST => !$memberships->getCommitteeHostMemberships(CommitteeMembershipCollection::EXCLUDE_SUPERVISORS)->isEmpty(),
                        Manager::INTEREST_KEY_COMMITTEE_FOLLOWER => $isFollower = !$memberships->getCommitteeFollowerMembershipsNotWaitingForApproval()->isEmpty(),
                        Manager::INTEREST_KEY_COMMITTEE_NO_FOLLOWER => !$isFollower,
                        Manager::INTEREST_KEY_CP_HOST => $adherent->isCitizenProjectAdministrator(),
                        Manager::INTEREST_KEY_REFERENT => $adherent->isReferent(),
                        Manager::INTEREST_KEY_DEPUTY => $adherent->isDeputy(),
                        Manager::INTEREST_KEY_REC => $adherent->isCoordinatorCitizenProjectSector(),
                        Manager::INTEREST_KEY_COORDINATOR => $adherent->isCoordinatorCommitteeSector(),
                        Manager::INTEREST_KEY_PROCURATION_MANAGER => $adherent->isProcurationManager(),
                        Manager::INTEREST_KEY_ASSESSOR_MANAGER => $adherent->isAssessorManager(),
                        Manager::INTEREST_KEY_BOARD_MEMBER => $adherent->isBoardMember(),
                    ])
                ),
                true
            )
        );
    }

    private function getAdherentActiveTags(Adherent $adherent): array
    {
        $tags = $adherent->getReferentTagCodes();

        if (PostAddress::FRANCE !== $adherent->getCountry()) {
            $tags[] = ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG;
        }

        if ($adherent->isCertified()) {
            $tags[] = MailchimpSegmentTagEnum::CERTIFIED;
        }

        if ($adherent->hasVotingCommitteeMembership()) {
            $tags[] = MailchimpSegmentTagEnum::COMMITTEE_VOTER;
        }

        return $tags;
    }

    private function getInactiveTags(Adherent $adherent): array
    {
        $tags = [];

        if (PostAddress::FRANCE === $adherent->getCountry()) {
            $tags[] = ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG;
        }

        if (!$adherent->isCertified()) {
            $tags[] = MailchimpSegmentTagEnum::CERTIFIED;
        }

        if (!$adherent->hasVotingCommitteeMembership()) {
            $tags[] = MailchimpSegmentTagEnum::COMMITTEE_VOTER;
        }

        return $tags;
    }
}
