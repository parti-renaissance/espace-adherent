<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="referent", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="referent_slug_unique", columns="slug")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LegislativeCandidateRepository")
 *
 * @Algolia\Index(autoIndex=false)
 * @UniqueEntity(fields="slug", groups="Admin")
 */
class Referent implements EntityMediaInterface
{
    use EntityPersonNameTrait;
    use EntityMediaTrait;

    const CAREERS = [
        'legislative_candidate.careers.1',
        'legislative_candidate.careers.2',
    ];

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Choice(
     *   callback = {"AppBundle\ValueObject\Genders", "all"},
     *   message="common.gender.invalid_choice",
     *   strict=true,
     *   groups="Admin"
     * )
     */
    private $gender;

    /**
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Email(groups="Admin")
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups="Admin")
     */
    private $emailAddress;

    /**
     * @ORM\Column(length=100)
     * @Gedmo\Slug(fields={"firstName","lastName"})
     *
     * @Assert\Regex(pattern="/^[a-z0-9-]+$/", message="legislative_candidate.slug.invalid", groups="Admin")
     */
    private $slug;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="legislative_candidate.facebook_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     */
    private $facebookPageUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="legislative_candidate.twitter_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     */
    private $twitterPageUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     */
    private $donationPageUrl;

    /**
     * @ORM\Column(nullable=true)
     * @Assert\Url(groups="Admin")
     */
    private $websiteUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $geojson;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Embedded(class="ManagedArea", columnPrefix="managed_area_")
     *
     * @var ManagedArea
     */
    private $managedArea;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getProfilePicture(): ?Media
    {
        return $this->media;
    }

    public function getFacebookPageUrl(): ?string
    {
        return $this->facebookPageUrl;
    }

    public function setFacebookPageUrl(?string $facebookPageUrl): void
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function getTwitterPageUrl(): ?string
    {
        return $this->twitterPageUrl;
    }

    public function setTwitterPageUrl(?string $twitterPageUrl): void
    {
        $this->twitterPageUrl = $twitterPageUrl;
    }

    public function getDonationPageUrl(): ?string
    {
        return $this->donationPageUrl;
    }

    public function setDonationPageUrl(?string $donationPageUrl): void
    {
        $this->donationPageUrl = $donationPageUrl;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): void
    {
        $this->websiteUrl = $websiteUrl;
    }

    public function getGeojson(): ?string
    {
        return $this->geojson;
    }

    public function setGeojson(?string $geojson): void
    {
        $this->geojson = $geojson;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function hasWebPages(): bool
    {
        return $this->websiteUrl || $this->twitterPageUrl || $this->facebookPageUrl || $this->donationPageUrl;
    }

    /**
     * @return mixed
     */
    public function getManagedArea()
    {
        return $this->managedArea;
    }

    /**
     * @param mixed $managedArea
     */
    public function setManagedArea($managedArea)
    {
        $this->managedArea = $managedArea;
    }
}
