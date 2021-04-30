<?php

namespace App\Mailchimp\Synchronisation\MemberRequest;

use App\Coalition\CoalitionMemberValueObject;
use App\Entity\Coalition\Coalition;
use App\Entity\PostAddress;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use App\Repository\Coalition\CauseRepository;
use App\Repository\Coalition\CoalitionRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class CoalitionMemberRequestBuilder extends AbstractMemberRequestBuilder
{
    public const INTEREST_KEY_CAUSE_SUBSCRIPTION = 'cause_subscription';
    public const INTEREST_KEY_COALITION_SUBSCRIPTION = 'coalition_subscription';

    private $mailchimpObjectIdMapping;
    private $causeRepository;
    private $coalitionRepository;
    private $translator;

    private $firstName;
    private $lastName;
    private $gender;
    private $zone;
    private $source;
    private $activeTags = [];

    public function __construct(
        MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        CauseRepository $causeRepository,
        CoalitionRepository $coalitionRepository,
        TranslatorInterface $translator
    ) {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
        $this->causeRepository = $causeRepository;
        $this->coalitionRepository = $coalitionRepository;
        $this->translator = $translator;
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

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function setZone(?string $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function setActiveTags(array $activeTags): self
    {
        $this->activeTags = $activeTags;

        return $this;
    }

    public function updateFromValueObject(CoalitionMemberValueObject $contact): self
    {
        return $this
            ->setEmail($contact->getEmail())
            ->setFirstName($contact->getFirstName())
            ->setLastName($contact->getLastName())
            ->setGender($contact->getGender())
            ->setZone($contact->isAdherent()
                ? (($postAddress = $contact->getPostAddress())
                    ? (PostAddress::FRANCE === $postAddress->getCountry()
                        ? \sprintf('%s %s', $postAddress->getPostalCode(), $postAddress->getCityName())
                        : \sprintf('%s %s', $postAddress->getCountry(), $postAddress->getCountryName()))
                    : null)
                : (($zone = $contact->getZone())
                    ? \sprintf('%s %s',
                $zone->isCountry() ? $zone->getCode() : $zone->getPostalCodeAsString(), $zone->getName())
                    : null)
            )
             ->setSource($contact->getSource())
             ->setInterests($this->buildInterestArray($contact))
             ->setActiveTags($this->buildActiveTags($contact))
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
            $mergeFields[MemberRequest::MERGE_FIELD_GENDER] = $this->translator->trans('common.'.$this->gender);
        }

        if ($this->zone) {
            $mergeFields[MemberRequest::MERGE_FIELD_ZONE] = $this->zone;
        }

        if ($this->source) {
            $mergeFields[MemberRequest::MERGE_FIELD_SOURCE] = $this->source;
        }

        return $mergeFields;
    }

    private function buildInterestArray(CoalitionMemberValueObject $contact): array
    {
        $coalitions = $this->coalitionRepository->findByFollower($contact->getEmail(), $contact->isAdherent());

        return array_replace(
            // By default all interests are disabled (`false` value) for a member
            array_fill_keys($ids = $this->mailchimpObjectIdMapping->getCoalitionsInterestIds(), false),

            // Activate coalitions
            array_fill_keys(
                array_intersect_key(
                    $ids,
                    array_flip(
                        array_map(
                            static function (Coalition $coalition) { return $coalition->getName(); },
                            $coalitions
                        )
                    )
                ),
                true
            ),

            // Activate email notifications
            array_fill_keys(
                array_intersect_key(
                    $ids,
                    array_filter([
                        self::INTEREST_KEY_CAUSE_SUBSCRIPTION => $contact->hasCauseSubscription(),
                        self::INTEREST_KEY_COALITION_SUBSCRIPTION => $contact->hasCoalitionSubscription(),
                    ])
                ),
                true
            )
        );
    }

    private function buildActiveTags(CoalitionMemberValueObject $contact): array
    {
        $tags = [];
        $email = $contact->getEmail();

        if ($contact->isAdherent()) {
            $coalitions = $this->coalitionRepository->findByCauseAuthor($email);
        } else {
            $coalitions = [];
        }
        $causes = $this->causeRepository->findByFollower($email, $contact->isAdherent());

        // cause author
        foreach ($coalitions as $coalition) {
            $tags[] = 'Porteur '.$coalition->getName();
        }

        if (\count($coalitions) > 0) {
            $tags[] = 'Porteur';
        }

        // cause follower
        foreach ($causes as $cause) {
            $tags[] = 'cause_'.$cause->getId();
        }

        return $tags;
    }

    public function createMemberTagsRequest(string $memberIdentifier, array $removedTags = []): MemberTagsRequest
    {
        $request = new MemberTagsRequest($memberIdentifier);

        // all tags are set as removed
        foreach ($removedTags as $tagName) {
            $request->addTag($tagName, false);
        }

        // add only active tags
        foreach ($this->activeTags as $tagName) {
            $request->addTag($tagName);
        }

        return $request;
    }
}
