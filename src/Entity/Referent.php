<?php

namespace App\Entity;

use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use App\Repository\ReferentRepository;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReferentRepository::class)]
#[UniqueEntity(fields: ['slug'], groups: ['Admin'])]
class Referent implements EntityMediaInterface
{
    use EntityPersonNameTrait;
    use EntityMediaTrait;

    public const ENABLED = 'ENABLED';
    public const DISABLED = 'DISABLED';

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice', groups: ['Admin'])]
    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(length: 6)]
    private $gender;

    #[Assert\Email(groups: ['Admin'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['Admin'])]
    #[ORM\Column(length: 100, nullable: true)]
    private $emailAddress;

    #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'legislative_candidate.slug.invalid', groups: ['Admin'])]
    #[Gedmo\Slug(fields: ['firstName', 'lastName'])]
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

    #[ORM\Column(type: 'text', nullable: true)]
    private $geojson;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[Assert\NotBlank]
    #[ORM\Column]
    private $areaLabel = '';

    /**
     * @var ReferentArea[]|Collection
     */
    #[Assert\Count(min: 1, groups: ['Admin'])]
    #[ORM\InverseJoinColumn(name: 'area_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(name: 'referent_id', referencedColumnName: 'id')]
    #[ORM\JoinTable(name: 'referent_areas')]
    #[ORM\ManyToMany(targetEntity: ReferentArea::class, fetch: 'EAGER')]
    private $areas;

    #[ORM\Column(length: 10, options: ['default' => 'DISABLED'])]
    private $status = self::ENABLED;

    /**
     * @var Collection|ReferentPersonLink[]
     */
    #[ORM\OneToMany(mappedBy: 'referent', targetEntity: ReferentPersonLink::class, cascade: ['persist'])]
    private $referentPersonLinks;

    public function __construct()
    {
        $this->areas = new ArrayCollection();
        $this->referentPersonLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->twitterPageUrl || $this->facebookPageUrl;
    }

    public function getAreaLabel()
    {
        return $this->areaLabel;
    }

    public function setAreaLabel($areaLabel): void
    {
        $this->areaLabel = $areaLabel;
    }

    public function addArea(ReferentArea $referentArea): void
    {
        if (!$this->areas->contains($referentArea)) {
            $this->areas->add($referentArea);
        }
    }

    public function removeArea(ReferentArea $referentArea): void
    {
        $this->areas->removeElement($referentArea);
    }

    public function getAreas()
    {
        return $this->areas;
    }

    public function getAreasIdAsString(): string
    {
        if ($this->areas->isEmpty()) {
            return '';
        }

        $areasIds = [];

        foreach ($this->areas as $area) {
            $areasIds[] = $area->getId();
        }

        return implode(',', $areasIds);
    }

    public function getAreasToString(): string
    {
        if ($this->areas->isEmpty()) {
            return '';
        }

        $areasIds = [];

        foreach ($this->areas as $area) {
            $areasIds[] = (string) $area;
        }

        return implode(',', $areasIds);
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

    /**
     * @return Collection|ReferentPersonLink[]
     */
    public function getReferentPersonLinks(): Collection
    {
        return $this->referentPersonLinks;
    }

    /**
     * @return Collection|ReferentPersonLink[]
     */
    public function getReferentPersonLinksWithExistingAdherent(): Collection
    {
        return $this->referentPersonLinks->filter(function (ReferentPersonLink $personLink) {
            return null !== $personLink->getAdherent();
        });
    }

    public function setReferentPersonLinks(Collection $referentPersonLinks): void
    {
        $this->referentPersonLinks = $referentPersonLinks;
    }
}
