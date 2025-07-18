<?php

namespace App\Mailchimp\Synchronisation;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Entity\Adherent;
use App\Entity\Campus\Registration;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneTagEnum;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\SubscriptionType;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\MailchimpSegment\MailchimpSegmentTagEnum;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository;
use App\Repository\DonationRepository;
use App\Repository\Geo\ZoneRepository;
use App\Utils\PhoneNumberUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RequestBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $publicId;
    private $email;
    private $phone;
    private $gender;
    private $firstName;
    private $lastName;
    private ?\DateTimeInterface $birthDay = null;
    private $city;
    private $zipCode;
    private $countryName;
    private $adhesionDate;
    private ?\DateTime $campusRegisteredAt = null;
    private ?bool $isCertified = null;
    private ?bool $isAdherent = null;
    private ?string $committeeUuid = null;

    private $interests;
    private ?\DateTime $firstMembershipDonation = null;
    private ?\DateTime $lastMembershipDonation = null;
    private ?string $source = null;
    private ?array $adherentTags = null;
    private ?array $electTags = null;

    private $activeTags = [];
    private $inactiveTags = [];
    private $favoriteCities;
    private $favoriteCitiesCodes;
    private $takenForCity = false;
    private $isSubscribeRequest = true;

    private ?\DateTime $inscriptionDate = null;
    private ?\DateTime $confirmationDate = null;
    private ?\DateTime $ticketSentAt = null;
    private ?\DateTime $ticketScannedAt = null;
    private ?string $ticketCustomDetail = null;
    private ?bool $isVolunteer = null;
    private ?bool $isJAM = null;
    private ?string $visitDay = null;
    private ?string $accessibility = null;
    private ?string $transport = null;
    private ?string $accommodation = null;
    private ?bool $isTransportNeeds = null;
    private ?bool $isWithDiscount = null;
    private ?UuidInterface $participantUuid = null;
    private ?string $status = null;

    private ?string $utmSource = null;
    private ?string $utmCampaign = null;

    private ?array $mandateTypes = null;
    private ?array $declaredMandates = null;

    /** @var Zone[] */
    private array $subZones = [];
    /** @var Zone[] */
    private array $zones = [];
    /** @var int[]|null */
    private ?array $donationYears = null;

    private $teamCode;

    private $codeCanton;
    private $codeDepartment;
    private $codeRegion;

    private ?string $loginGroup = null;

    public function __construct(
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly ElectedRepresentativeTagsBuilder $electedRepresentativeTagsBuilder,
        private readonly ElectedRepresentativeAdherentMandateRepository $mandateRepository,
        private readonly DonationRepository $donationRepository,
        private readonly TagTranslator $tagTranslator,
        private readonly ZoneRepository $zoneRepository,
        private readonly TranslatorInterface $translator,
    ) {
        $this->logger = new NullLogger();
    }

    public function createReplaceEmailRequest(string $oldEmail, string $newEmail): MemberRequest
    {
        $request = new MemberRequest($oldEmail);
        $request->setEmailAddress($newEmail);

        return $request;
    }

    public function updateFromAdherent(Adherent $adherent): self
    {
        return $this
            ->setPublicId($adherent->getPublicId())
            ->setEmail($adherent->getEmailAddress())
            ->setGender($adherent->getGender())
            ->setFirstName($adherent->getFirstName())
            ->setLastName($adherent->getLastName())
            ->setBirthDay($adherent->getBirthdate())
            ->setZipCode($adherent->getPostalCode())
            ->setCity($adherent->getCityName())
            ->setCountryName($adherent->getCountryName())
            ->setAdhesionDate($adherent->getRegisteredAt())
            ->setFirstMembershipDonation($adherent->getFirstMembershipDonation())
            ->setLastMembershipDonation($adherent->getLastMembershipDonation())
            ->setSource($adherent->getSource())
            ->setAdherentTags($adherent->tags)
            ->setActiveTags($this->getAdherentActiveTags($adherent))
            ->setInactiveTags($this->getInactiveTags($adherent))
            ->setIsSubscribeRequest($adherent->isEnabled() && $adherent->isEmailSubscribed())
            ->setZones($adherent->getZones())
            ->setDonationYears($this->findDonationYears($adherent))
            ->setCommitteeUuid($adherent->getCommitteeMembership()?->getCommitteeUuid())
            ->setMandateTypes($this->mandateRepository->getAdherentMandateTypes($adherent))
            ->setDeclaredMandates($adherent->getMandates() ?? [])
            ->setCampusRegisteredAt($adherent->getValidCampusRegistration())
            ->setTeamCode($adherent)
            ->setIsCertified($adherent->isCertified())
            ->setLoginGroup($adherent->getLastLoginGroup())
            ->setInterests($this->buildInterestArray($adherent))
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

    public function updateFromNationalEventInscription(EventInscription $eventInscription): self
    {
        return $this
            ->setEmail($eventInscription->addressEmail)
            ->setPhone(PhoneNumberUtils::format($eventInscription->phone))
            ->setFirstName($eventInscription->firstName)
            ->setLastName($eventInscription->lastName)
            ->setGender($eventInscription->gender)
            ->setBirthDay($eventInscription->birthdate)
            ->setAdherentTags(array_intersect(TagEnum::getAdherentTags(), $eventInscription->adherent?->tags ?? []))
            ->setElectTags(array_intersect(TagEnum::getElectTags(), $eventInscription->adherent?->tags ?? []))
            ->setPublicId($eventInscription->adherent?->getPublicId())
            ->setZipCode($eventInscription->postalCode)
            ->setInscriptionDate($eventInscription->getCreatedAt())
            ->setConfirmationDate($eventInscription->confirmedAt)
            ->setTicketSentAt($eventInscription->ticketSentAt)
            ->setTicketScannedAt($eventInscription->ticketScannedAt)
            ->setTicketCustomDetail($eventInscription->ticketCustomDetail)
            ->setIsVolunteer($eventInscription->volunteer)
            ->setIsJAM($eventInscription->isJAM)
            ->setVisitDay($eventInscription->visitDay)
            ->setAccessibility($eventInscription->accessibility)
            ->setIsTransportNeeds($eventInscription->transportNeeds)
            ->setTransport($eventInscription->transport)
            ->setAccommodation($eventInscription->accommodation)
            ->setIsWithDiscount($eventInscription->withDiscount)
            ->setParticipantUuid($eventInscription->getUuid())
            ->setStatus($eventInscription->status)
            ->setUtmSource($eventInscription->utmSource)
            ->setUtmCampaign($eventInscription->utmCampaign)
            ->setZones(new ArrayCollection($this->zoneRepository->findByPostalCode($eventInscription->postalCode)))
        ;
    }

    public function updateFromDataSurvey(JemarcheDataSurvey $dataSurvey, array $zones): self
    {
        $this
            ->setEmail($dataSurvey->getEmailAddress())
            ->setFirstName($dataSurvey->getFirstName())
            ->setLastName($dataSurvey->getLastName())
            ->setZipCode($dataSurvey->getPostalCode())
        ;

        foreach ($zones as $zone) {
            $this->setZoneCode($zone);
        }

        return $this;
    }

    public function setPublicId(?string $publicId): self
    {
        $this->publicId = $publicId;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

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

    public function setIsCertified(?bool $isCertified = null): self
    {
        $this->isCertified = $isCertified;

        return $this;
    }

    public function setCommitteeUuid(?UuidInterface $committeeUuid): self
    {
        $this->committeeUuid = $committeeUuid ? $committeeUuid->toString() : '';

        return $this;
    }

    public function setIsAdherent(?bool $isAdherent = null): self
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

    public function setFirstMembershipDonation(?\DateTimeInterface $firstMembershipDonation): self
    {
        $this->firstMembershipDonation = $firstMembershipDonation;

        return $this;
    }

    public function setLastMembershipDonation(?\DateTimeInterface $lastMembershipDonation): self
    {
        $this->lastMembershipDonation = $lastMembershipDonation;

        return $this;
    }

    public function setCampusRegisteredAt(?Registration $campusRegistration): self
    {
        if ($campusRegistration) {
            $this->campusRegisteredAt = $campusRegistration->registeredAt;
        }

        return $this;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function setAdherentTags(?array $tags): self
    {
        $this->adherentTags = array_filter(array_map(fn (string $key) => $this->tagTranslator->trans($key), $tags));

        return $this;
    }

    public function setElectTags(?array $tags): self
    {
        $this->electTags = array_filter(array_map(fn (string $key) => $this->tagTranslator->trans($key), $tags));

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

    public function setTakenForCity(?string $takenForCity): self
    {
        $this->takenForCity = $takenForCity;

        return $this;
    }

    public function setLoginGroup(?string $loginGroup): self
    {
        $this->loginGroup = $loginGroup;

        return $this;
    }

    /**
     * @param Collection|Zone[] $zones
     */
    public function setZones(Collection $zones): self
    {
        foreach ($zones as $zone) {
            $this->setZone($zone);
        }

        foreach ($zones as $zone) {
            foreach ($zone->getParents() as $parent) {
                $this->setZone($parent);
            }
        }

        return $this;
    }

    public function setDonationYears(array $years): self
    {
        $this->donationYears = $years;

        return $this;
    }

    private function setZone(Zone $zone): void
    {
        if (!$zone->isActive()) {
            return;
        }

        if ($zone->hasTag(ZoneTagEnum::SUB_ZONE)) {
            if (!isset($this->subZones[$zone->getType()])) {
                $this->subZones[$zone->getType()] = $zone;
            }

            return;
        }

        if (!isset($this->zones[$zone->getType()])) {
            $this->zones[$zone->getType()] = $zone;
        }
    }

    private function setZoneCode(Zone $zone): void
    {
        switch ($zone->getType()) {
            case Zone::CANTON:
                $this->codeCanton = $zone->getCode();

                break;
            case Zone::DEPARTMENT:
                $this->codeDepartment = $zone->getCode();

                break;
            case Zone::REGION:
                $this->codeRegion = $zone->getCode();

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

    public function setMandateTypes(array $mandateTypes): self
    {
        $this->mandateTypes = $mandateTypes;

        return $this;
    }

    public function setDeclaredMandates(array $declaredMandates): self
    {
        $this->declaredMandates = $declaredMandates;

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

        if ($this->publicId) {
            $mergeFields[MemberRequest::MERGE_FIELD_PUBLIC_ID] = $this->publicId;
        }

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

        if (null !== $this->isCertified) {
            $mergeFields[MemberRequest::MERGE_FIELD_CERTIFIED] = $this->isCertified ? 'oui' : 'non';
        }

        if (null !== $this->committeeUuid) {
            $mergeFields[MemberRequest::MERGE_FIELD_COMMITTEE] = $this->committeeUuid;
        }

        if (null !== $this->isAdherent) {
            $mergeFields[MemberRequest::MERGE_FIELD_ADHERENT] = $this->isAdherent ? 'oui' : 'non';
        }

        if ($this->firstMembershipDonation) {
            $mergeFields[MemberRequest::MERGE_FIELD_FIRST_MEMBERSHIP_DONATION] = $this->firstMembershipDonation->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->lastMembershipDonation) {
            $mergeFields[MemberRequest::MERGE_FIELD_LAST_MEMBERSHIP_DONATION] = $this->lastMembershipDonation->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->source) {
            $mergeFields[MemberRequest::MERGE_FIELD_SOURCE] = $this->source;
        }

        if ($this->adherentTags) {
            $mergeFields[MemberRequest::MERGE_FIELD_ADHERENT_TAGS] = implode(',', $this->adherentTags);
        }

        if ($this->city) {
            $mergeFields[MemberRequest::MERGE_FIELD_CITY] = \sprintf('%s (%s)', $this->city, $this->zipCode);
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

        if (false !== $this->takenForCity) {
            $mergeFields[MemberRequest::MERGE_FIELD_MUNICIPAL_TEAM] = (string) $this->takenForCity;
        }

        // Fill Zone merge field
        foreach (MemberRequest::ZONE_MERGE_FIELD_BY_ZONE_TYPE as $mergeField => $zoneType) {
            $mergeFields[$mergeField] = (string) ($this->zones[$zoneType] ?? null);
        }

        // Complete Zone merge field with sub zones (zone uses Zone tag `sub_zone`)
        foreach ($this->subZones as $zone) {
            $mergeField = array_search($zone->getType(), MemberRequest::ZONE_MERGE_FIELD_BY_ZONE_TYPE);
            if (!$mergeField) {
                continue;
            }

            if (empty($mergeFields[$mergeField])) {
                $mergeFields[$mergeField] = (string) $zone;
            } else {
                $mergeFields[$mergeField] .= \sprintf(' (%s)', $zone->getCode());
            }
        }

        if ($this->codeCanton) {
            $mergeFields[MemberRequest::MERGE_FIELD_CODE_CANTON] = $this->codeCanton;
        }

        if ($this->codeDepartment) {
            $mergeFields[MemberRequest::MERGE_FIELD_CODE_DEPARTMENT] = $this->codeDepartment;
        }

        if ($this->codeRegion) {
            $mergeFields[MemberRequest::MERGE_FIELD_CODE_REGION] = $this->codeRegion;
        }

        if ($this->teamCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_TEAM_CODE] = (string) $this->teamCode;
        }

        if ($this->loginGroup) {
            $mergeFields[MemberRequest::MERGE_FIELD_LAST_LOGIN_GROUP] = $this->loginGroup;
        }

        if (null !== $this->mandateTypes) {
            $mandateTypes = array_map(function (string $mandateType): string {
                return '"'.$mandateType.'"';
            }, $this->mandateTypes);

            $mergeFields[MemberRequest::MERGE_FIELD_MANDATE_TYPES] = implode(',', $mandateTypes);
        }

        if (null !== $this->declaredMandates) {
            $declaredMandates = array_map(function (string $declaredMandate): string {
                return '"'.$declaredMandate.'"';
            }, $this->declaredMandates);

            $mergeFields[MemberRequest::MERGE_FIELD_DECLARED_MANDATES] = implode(',', $declaredMandates);
        }

        if (null !== $this->campusRegisteredAt) {
            $mergeFields[MemberRequest::MERGE_FIELD_CAMPUS_REGISTRATION_DATE] = $this->campusRegisteredAt->format(MemberRequest::DATE_FORMAT);
        }

        if (null !== $this->donationYears) {
            $mergeFields[MemberRequest::MERGE_FIELD_DONATION_YEARS] = implode(',', $this->donationYears);
        }

        if ($this->inscriptionDate) {
            $mergeFields[MemberRequest::MERGE_FIELD_INSCRIPTION_DATE] = $this->inscriptionDate->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->confirmationDate) {
            $mergeFields[MemberRequest::MERGE_FIELD_CONFIRMATION_DATE] = $this->confirmationDate->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->ticketSentAt) {
            $mergeFields[MemberRequest::MERGE_FIELD_TICKET_SENT_AT] = $this->ticketSentAt->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->ticketScannedAt) {
            $mergeFields[MemberRequest::MERGE_FIELD_TICKET_SCANNED_AT] = $this->ticketScannedAt->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->ticketCustomDetail) {
            $mergeFields[MemberRequest::MERGE_FIELD_TICKET_CUSTOM_DETAIL] = $this->ticketCustomDetail;
        }

        if (null !== $this->isVolunteer) {
            $mergeFields[MemberRequest::MERGE_FIELD_IS_VOLUNTEER] = $this->isVolunteer ? 'oui' : 'non';
        }

        if (null !== $this->isJAM) {
            $mergeFields[MemberRequest::MERGE_FIELD_IS_JAM] = $this->isJAM ? 'oui' : 'non';
        }

        if ($this->visitDay) {
            $mergeFields[MemberRequest::MERGE_FIELD_VISIT_DAY] = $this->visitDay;
        }

        if ($this->accessibility) {
            $mergeFields[MemberRequest::MERGE_FIELD_ACCESSIBILITY] = $this->accessibility;
        }

        if (null !== $this->isTransportNeeds) {
            $mergeFields[MemberRequest::MERGE_FIELD_IS_TRANSPORT_NEEDS] = $this->isTransportNeeds ? 'oui' : 'non';
        }

        if (null !== $this->transport) {
            $mergeFields[MemberRequest::MERGE_FIELD_TRANSPORT] = $this->transport;
        }

        if (null !== $this->accommodation) {
            $mergeFields[MemberRequest::MERGE_FIELD_ACCOMODATION] = $this->accommodation;
        }

        if (null !== $this->isWithDiscount) {
            $mergeFields[MemberRequest::MERGE_FIELD_IS_WITH_DISCOUNT] = $this->isWithDiscount ? 'oui' : 'non';
        }

        if ($this->participantUuid) {
            $mergeFields[MemberRequest::MERGE_FIELD_PARTICIPANT_UUID] = $this->participantUuid->toString();
        }

        if ($this->status) {
            $mergeFields[MemberRequest::MERGE_FIELD_STATUS] = $this->translator->trans($this->status);
        }

        if ($this->utmSource) {
            $mergeFields[MemberRequest::MERGE_FIELD_UTM_SOURCE] = $this->utmSource;
        }

        if ($this->utmCampaign) {
            $mergeFields[MemberRequest::MERGE_FIELD_UTM_CAMPAIGN] = $this->utmCampaign;
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
                        Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR => $adherent->isSupervisor(false),
                        Manager::INTEREST_KEY_COMMITTEE_PROVISIONAL_SUPERVISOR => $adherent->isSupervisor(true),
                        Manager::INTEREST_KEY_COMMITTEE_HOST => (bool) ($membership = $adherent->getCommitteeMembership())?->isHostMember(),
                        Manager::INTEREST_KEY_COMMITTEE_FOLLOWER => $isFollower = (bool) $membership?->isFollower(),
                        Manager::INTEREST_KEY_COMMITTEE_NO_FOLLOWER => !$isFollower,
                        Manager::INTEREST_KEY_DEPUTY => $adherent->isDeputy(),
                        Manager::INTEREST_KEY_COORDINATOR => $adherent->isRegionalCoordinator(),
                        Manager::INTEREST_KEY_PROCURATION_MANAGER => $adherent->isProcurationsManager(),
                    ])
                ),
                true
            )
        );
    }

    private function getAdherentActiveTags(Adherent $adherent): array
    {
        $tags = [];

        if ($adherent->isCertified()) {
            $tags[] = MailchimpSegmentTagEnum::CERTIFIED;
        }

        if ($adherent->hasVotingCommitteeMembership()) {
            $tags[] = MailchimpSegmentTagEnum::COMMITTEE_VOTER;
        }

        return $tags;
    }

    private function findDonationYears(Adherent $adherent): array
    {
        return $this->donationRepository->getDonationYearsForAdherent($adherent);
    }

    private function getInactiveTags(Adherent $adherent): array
    {
        $tags = [];

        if (!$adherent->isCertified()) {
            $tags[] = MailchimpSegmentTagEnum::CERTIFIED;
        }

        if (!$adherent->hasVotingCommitteeMembership()) {
            $tags[] = MailchimpSegmentTagEnum::COMMITTEE_VOTER;
        }

        return $tags;
    }

    public function setInscriptionDate(?\DateTimeInterface $inscriptionDate): self
    {
        $this->inscriptionDate = $inscriptionDate;

        return $this;
    }

    public function setConfirmationDate(?\DateTimeInterface $confirmationDate): self
    {
        $this->confirmationDate = $confirmationDate;

        return $this;
    }

    public function setTicketSentAt(?\DateTimeInterface $ticketSentAt): self
    {
        $this->ticketSentAt = $ticketSentAt;

        return $this;
    }

    public function setTicketScannedAt(?\DateTimeInterface $ticketScannedAt): self
    {
        $this->ticketScannedAt = $ticketScannedAt;

        return $this;
    }

    public function setTicketCustomDetail(?string $ticketCustomDetail): self
    {
        $this->ticketCustomDetail = $ticketCustomDetail;

        return $this;
    }

    public function setIsVolunteer(?bool $isVolunteer = null): self
    {
        $this->isVolunteer = $isVolunteer;

        return $this;
    }

    public function setIsJAM(?bool $isJAM = null): self
    {
        $this->isJAM = $isJAM;

        return $this;
    }

    public function setVisitDay(?string $visitDay): self
    {
        $this->visitDay = $visitDay;

        return $this;
    }

    public function setAccessibility(?string $accessibility = null): self
    {
        $this->accessibility = $accessibility;

        return $this;
    }

    public function setIsTransportNeeds(?bool $isTransportNeeds = null): self
    {
        $this->isTransportNeeds = $isTransportNeeds;

        return $this;
    }

    public function setTransport(?string $transport = null): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function setAccommodation(?string $accommodation = null): self
    {
        $this->accommodation = $accommodation;

        return $this;
    }

    public function setParticipantUuid(?UuidInterface $participantUuid = null): self
    {
        $this->participantUuid = $participantUuid;

        return $this;
    }

    public function setStatus(?string $status = null): self
    {
        $this->status = $status;

        return $this;
    }

    public function setIsWithDiscount(?bool $isWithDiscount = null): self
    {
        $this->isWithDiscount = $isWithDiscount;

        return $this;
    }

    public function setUtmSource(?string $utmSource = null): self
    {
        $this->utmSource = $utmSource;

        return $this;
    }

    public function setUtmCampaign(?string $utmCampaign = null): self
    {
        $this->utmCampaign = $utmCampaign;

        return $this;
    }
}
