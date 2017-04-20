<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="legislative_candidates", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="legislative_candidates_slug_unique", columns="slug")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LegislativeRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class LegislativeCandidate
{
    use EntityPersonNameTrait;
    use EntityMediaTrait;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=100)
     * @Gedmo\Slug(fields={"firstName", "lastName"})
     */
    private $slug;

    /**
     * @ORM\Column(nullable=true)
     */
    private $facebookPageUrl;

    /**
     * @ORM\Column(nullable=true)
     */
    private $twitterPageUrl;

    /**
     * @ORM\Column(nullable=true)
     */
    private $donationPageUrl;

    /**
     * @ORM\Column(nullable=true)
     */
    private $websiteUrl;

    /**
     * @ORM\Column(length=100)
     */
    private $districtName;

    /**
     * @ORM\Column(length=10)
     */
    private $districtNumber;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\LegislativeDistrictZone")
     */
    private $districtZone;

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

    public function getProfilePicture(): ?string
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

    public function getDistrictName(): ?string
    {
        return $this->districtName;
    }

    public function setDistrictName(string $districtName): void
    {
        $this->districtName = $districtName;
    }

    public function getDistrictNumber(): ?string
    {
        return $this->districtNumber;
    }

    public function setDistrictNumber(string $districtNumber): void
    {
        $this->districtNumber = $districtNumber;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDistrictZone(): ?LegislativeDistrictZone
    {
        return $this->districtZone;
    }

    public function setDistrictZone(?LegislativeDistrictZone $districtZone): void
    {
        $this->districtZone = $districtZone;
    }
}
