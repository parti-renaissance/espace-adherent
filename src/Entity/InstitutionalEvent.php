<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Address\GeoCoder;
use AppBundle\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InstitutionalEventRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class InstitutionalEvent extends BaseEvent implements ReferentTaggableEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\InstitutionalEventCategory")
     */
    protected $category;

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $referentTags;

    public function getType(): ?string
    {
        return $this->type ?? self::INSTITUTIONAL_EVENT_TYPE;
    }

    public function getReportType(): string
    {
        return ReportType::COMMUNITY_EVENT;
    }

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $organizer,
        string $name,
        InstitutionalEventCategory $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        string $createdAt = null,
        string $slug = null,
        array $referentTags = [],
        string $timeZone = GeoCoder::DEFAULT_TIME_ZONE
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->setName($name);
        $this->slug = $slug;
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->participantsCount = 0;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->createdAt = new \DateTime($createdAt ?: 'now');
        $this->updatedAt = new \DateTime($createdAt ?: 'now');
        $this->status = self::STATUS_SCHEDULED;
        $this->referentTags = new ArrayCollection($referentTags);
        $this->timeZone = $timeZone;
    }

    /**
     * @return Collection|ReferentTag[]
     */
    public function getReferentTags(): Collection
    {
        return $this->referentTags;
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        if (!$this->referentTags->contains($referentTag)) {
            $this->referentTags->add($referentTag);
        }
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags->removeElement($referentTag);
    }

    public function clearReferentTags(): void
    {
        $this->referentTags->clear();
    }

    public function getCategory(): InstitutionalEventCategory
    {
        return $this->category;
    }

    public function getReferentTagsCodes(): array
    {
        return array_map(function (ReferentTag $referentTag) {
            return $referentTag->getCode();
        }, $this->referentTags->toArray());
    }

    public function isReferentEvent(): bool
    {
        return true;
    }
}
