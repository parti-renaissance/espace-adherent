<?php

namespace App\Entity;

use App\Repository\LegislativeCandidateRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LegislativeCandidateRepository::class)]
#[ORM\Table(name: 'legislative_candidates')]
#[UniqueEntity(fields: ['slug'], groups: ['Admin'])]
class LegislativeCandidate implements EntityMediaInterface
{
    use EntityPersonNameTrait;
    use EntityMediaTrait;

    public const CAREERS = [
        'legislative_candidate.careers.1',
        'legislative_candidate.careers.2',
    ];

    public const STATUS_NONE = 'none';
    public const STATUS_LOST = 'lost';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_WON = 'won';

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var int|null
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'integer')]
    private $position = 0;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice', groups: ['Admin'])]
    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(length: 6)]
    private $gender;

    #[Assert\Email(groups: ['Admin'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['Admin'])]
    #[ORM\Column(length: 100, nullable: true)]
    private $emailAddress;

    #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'legislative_candidate.slug.invalid', groups: ['Admin'])]
    #[Gedmo\Slug(fields: ['districtName'])]
    #[ORM\Column(length: 100, unique: true)]
    private $slug;

    #[Assert\Length(max: 255, groups: ['Admin'])]
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?facebook.com\/#', message: 'legislative_candidate.facebook_page_url.invalid', groups: ['Admin'])]
    #[Assert\Url(groups: ['Admin'])]
    #[ORM\Column(nullable: true)]
    private $facebookPageUrl;

    #[Assert\Length(max: 255, groups: ['Admin'])]
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?twitter.com\/#', message: 'legislative_candidate.twitter_page_url.invalid', groups: ['Admin'])]
    #[Assert\Url(groups: ['Admin'])]
    #[ORM\Column(nullable: true)]
    private $twitterPageUrl;

    #[Assert\Length(max: 255, groups: ['Admin'])]
    #[Assert\Url(groups: ['Admin'])]
    #[ORM\Column(nullable: true)]
    private $donationPageUrl;

    #[Assert\Url(groups: ['Admin'])]
    #[ORM\Column(nullable: true)]
    private $websiteUrl;

    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(length: 100)]
    private $districtName;

    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(type: 'smallint')]
    private $districtNumber;

    #[ORM\Column(type: 'text', nullable: true)]
    private $geojson;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[Assert\NotBlank(groups: ['Admin'])]
    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: LegislativeDistrictZone::class, fetch: 'EAGER')]
    private $districtZone;

    #[Assert\Choice(callback: 'getCareerChoices', message: 'legislative_candidate.carreer.invalid', groups: ['Admin'])]
    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column]
    private $career;

    #[Assert\Choice(callback: 'getStatuses', groups: ['Admin'])]
    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(length: 20, options: ['default' => 'none'])]
    private $status = self::STATUS_NONE;

    public static function getCareerChoices(): array
    {
        return self::CAREERS;
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NONE,
            self::STATUS_LOST,
            self::STATUS_QUALIFIED,
            self::STATUS_WON,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
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

    public function getDistrictZone(): ?LegislativeDistrictZone
    {
        return $this->districtZone;
    }

    public function setDistrictZone(?LegislativeDistrictZone $districtZone): void
    {
        $this->districtZone = $districtZone;
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

    public function getCareer(): ?string
    {
        return $this->career;
    }

    public function setCareer(string $career): void
    {
        $this->career = $career;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }
}
