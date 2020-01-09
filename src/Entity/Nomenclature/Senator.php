<?php

namespace AppBundle\Entity\Nomenclature;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityMediaInterface;
use AppBundle\Entity\EntityMediaTrait;
use AppBundle\Entity\EntityPersonNameTrait;
use AppBundle\Entity\Media;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="nomenclature_senator", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="senator_slug_unique", columns="slug")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Nomenclature\SenatorRepository")
 *
 * @Algolia\Index(autoIndex=false)
 * @UniqueEntity(fields="slug")
 */
class Senator implements EntityMediaInterface
{
    use EntityPersonNameTrait;
    use EntityMediaTrait;

    public const ENABLED = 'ENABLED';
    public const DISABLED = 'DISABLED';

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank
     * @Assert\Choice(
     *     callback={"AppBundle\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true
     * )
     */
    private $gender;

    /**
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Email
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @ORM\Column(length=100)
     * @Gedmo\Slug(fields={"firstName", "lastName"})
     *
     * @Assert\Regex(pattern="/^[a-z0-9-]+$/", message="legislative_candidate.slug.invalid")
     */
    private $slug;

    /**
     * @ORM\Column(nullable=true)
     * @Assert\Url
     */
    private $websiteUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="legislative_candidate.facebook_page_url.invalid")
     * @Assert\Length(max=255)
     */
    private $facebookPageUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="legislative_candidate.twitter_page_url.invalid")
     * @Assert\Length(max=255)
     */
    private $twitterPageUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(length=10, options={"default": "DISABLED"})
     */
    private $status = self::ENABLED;

    /**
     * @ORM\ManyToOne(targetEntity=SenatorArea::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     */
    private $area;

    public function __construct(
        ?SenatorArea $area = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $gender = null,
        ?string $emailAddress = null,
        ?string $slug = null,
        ?string $description = null,
        ?string $websiteUrl = null,
        ?string $facebookPageUrl = null,
        ?string $twitterPageUrl = null,
        ?string $status = self::ENABLED
    ) {
        $this->area = $area;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->gender = $gender;
        $this->emailAddress = $emailAddress;
        $this->slug = $slug;
        $this->description = $description;
        $this->websiteUrl = $websiteUrl;
        $this->facebookPageUrl = $facebookPageUrl;
        $this->twitterPageUrl = $twitterPageUrl;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
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

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): void
    {
        $this->websiteUrl = $websiteUrl;
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
        return $this->websiteUrl || $this->twitterPageUrl || $this->facebookPageUrl;
    }

    public function isEnabled(): bool
    {
        return self::ENABLED === $this->status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getArea(): ?SenatorArea
    {
        return $this->area;
    }

    public function setArea(?SenatorArea $area): void
    {
        $this->area = $area;
    }
}
