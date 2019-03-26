<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcurationManagerRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class AdherentReferentData implements EntityMediaInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     * @ORM\JoinTable(
     *     name="referent_managed_areas_tags",
     *     joinColumns={
     *         @ORM\JoinColumn(name="referent_managed_area_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="referent_tag_id", referencedColumnName="id")
     *     }
     * )
     */
    private $tags;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $markerLatitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $markerLongitude;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true)
     */
    private $media;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayMedia = true;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="adherent.facebook_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255)
     */
    private $facebookPageUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="adherent.twitter_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255)
     */
    private $twitterPageUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?linkedin.com\/#", message="adherent.linked_in_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255)
     */
    private $linkedInPageUrl;

    /**
     * @ORM\Column(nullable=true)
     */
    private $tagsLabel;

    public function __construct(
        array $tags = [],
        string $latitude = null,
        string $longitude = null,
        string $tagsLabel = null
    ) {
        $this->markerLatitude = $latitude;
        $this->markerLongitude = $longitude;
        $this->tags = new ArrayCollection($tags);
        $this->tagsLabel = $tagsLabel;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ReferentTag[]|Collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ReferentTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(ReferentTag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getOnlyManagedCountryCodes($value): array
    {
        return array_values(array_filter(array_map(function (ReferentTag $tag) use ($value) {
            if (ctype_alpha($tag->getCode())
                && (!$value || ($value && 0 === stripos($tag->getName(), $value)))) {
                return [$tag->getCode() => $tag->getName()];
            }
        }, $this->tags->toArray())));
    }

    public function getMarkerLatitude(): ?string
    {
        return $this->markerLatitude;
    }

    public function setMarkerLatitude(?string $markerLatitude): void
    {
        if (!$markerLatitude) {
            $markerLatitude = null;
        }

        $this->markerLatitude = $markerLatitude;
    }

    public function getMarkerLongitude(): ?string
    {
        return $this->markerLongitude;
    }

    public function setMarkerLongitude(?string $markerLongitude): void
    {
        if (!$markerLongitude) {
            $markerLongitude = null;
        }

        $this->markerLongitude = $markerLongitude;
    }

    public function getReferentTagCodes(): array
    {
        return array_map(function (ReferentTag $tag) { return $tag->getCode(); }, $this->getTags()->toArray());
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function getDisplayMedia(): bool
    {
        return $this->displayMedia;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getFacebookPageUrl(): ?string
    {
        return $this->facebookPageUrl;
    }

    public function getTwitterPageUrl(): ?string
    {
        return $this->twitterPageUrl;
    }

    public function getLinkedInPageUrl(): ?string
    {
        return $this->linkedInPageUrl;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }

    public function setDisplayMedia(bool $displayMedia): void
    {
        $this->displayMedia = $displayMedia;
    }

    public function setFacebookPageUrl(?string $facebookPageUrl): void
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function setTwitterPageUrl(?string $twitterPageUrl): void
    {
        $this->twitterPageUrl = $twitterPageUrl;
    }

    public function setLinkedInPageUrl(?string $linkedInPageUrl): void
    {
        $this->linkedInPageUrl = $linkedInPageUrl;
    }

    public function displayMedia(): bool
    {
        return $this->displayMedia;
    }

    public function getTagsLabel(): ?string
    {
        return $this->tagsLabel;
    }

    public function setTagsLabel(?string $tagsLabel): void
    {
        $this->tagsLabel = $tagsLabel;
    }

    public function getProfilePicture(): ?Media
    {
        return $this->media;
    }

    public function hasWebPages(): bool
    {
        return $this->twitterPageUrl || $this->facebookPageUrl || $this->linkedInPageUrl;
    }

    public function tagsIdAsString(): string
    {
        if ($this->tags->isEmpty()) {
            return '';
        }

        $tagsIds = [];

        foreach ($this->tags as $tag) {
            $tagsIds[] = $tag->getId();
        }

        return implode(',', $tagsIds);
    }
}
