<?php

namespace App\Entity\LocalSite;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filter\LocalSiteScopeFilter;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\UnLayerJsonContentTrait;
use App\Validator\ZoneInScopeZones as AssertZoneInScopeZones;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "filters": {LocalSiteScopeFilter::class},
 *         "normalization_context": {
 *             "groups": {"local_site_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"local_site_write"}
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'local_site')"
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/local_sites/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *         },
 *         "put": {
 *             "path": "/v3/local_sites/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/local_sites",
 *             "normalization_context": {
 *                 "groups": {"local_site_read_list"}
 *             }
 *         },
 *         "post": {
 *             "path": "/v3/local_sites",
 *         }
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocalSite\LocalSiteRepository")
 *
 * @UniqueEntity(fields={"zone"}, message="local_site.zone.not_unique")
 * @UniqueEntity(fields={"slug"})
 */
class LocalSite
{
    use EntityIdentityTrait;
    use UnLayerJsonContentTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @Groups({"local_site_read", "local_site_read_list", "local_site_write"})
     */
    private ?string $content = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\NotBlank
     * @Assert\Expression(
     *     "value and value.getType() === constant('App\\Entity\\Geo\\Zone::DEPARTMENT')",
     *     message="local_site.zone.type.not_valid"
     * )
     * @AssertZoneInScopeZones
     *
     * @Groups({"local_site_read", "local_site_read_list", "local_site_write"})
     */
    private ?Zone $zone = null;

    /**
     * @ORM\Column(unique=true)
     * @Gedmo\Slug(handlers={
     *     @Gedmo\SlugHandler(class="App\LocalSite\LocalSiteSlugHandler")
     * }, fields={"slug"}, updatable=true)
     *
     * @Groups({"local_site_read", "local_site_read_list"})
     */
    private ?string $slug = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
