<?php

namespace App\Entity\DepartmentSite;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filter\DepartmentSiteScopeFilter;
use App\DepartmentSite\DepartmentSiteSlugHandler;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\UnlayerJsonContentTrait;
use App\Repository\DepartmentSite\DepartmentSiteRepository;
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
 *         "filters": {DepartmentSiteScopeFilter::class},
 *         "normalization_context": {
 *             "groups": {"department_site_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"department_site_write"}
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'department_site')"
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/department_sites/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *         },
 *         "put": {
 *             "path": "/v3/department_sites/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "normalization_context": {
 *                 "groups": {"department_site_post_write"}
 *             },
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/department_sites",
 *             "normalization_context": {
 *                 "groups": {"department_site_read_list"}
 *             }
 *         },
 *         "post": {
 *             "path": "/v3/department_sites",
 *             "normalization_context": {
 *                 "groups": {"department_site_post_write"}
 *             },
 *         }
 *     }
 * )
 */
#[ORM\Entity(repositoryClass: DepartmentSiteRepository::class)]
#[UniqueEntity(fields: ['zone'], message: 'department_site.zone.not_unique')]
#[UniqueEntity(fields: ['slug'])]
class DepartmentSite
{
    use EntityIdentityTrait;
    use UnlayerJsonContentTrait;
    use EntityTimestampableTrait;

    #[Assert\NotBlank]
    #[Groups(['department_site_read', 'department_site_write'])]
    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    /**
     * @AssertZoneInScopeZones
     */
    #[Assert\Expression("value and (value.getType() === constant('App\\\\Entity\\\\Geo\\\\Zone::DEPARTMENT') or (value.getType() === constant('App\\\\Entity\\\\Geo\\\\Zone::CUSTOM') and value.getCode() === constant('App\\\\Entity\\\\Geo\\\\Zone::FDE_CODE')))", message: 'department_site.zone.type.not_valid')]
    #[Assert\NotBlank]
    #[Groups(['department_site_read', 'department_site_read_list', 'department_site_write'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Zone::class)]
    private ?Zone $zone = null;

    #[Gedmo\Slug(fields: ['content'])]
    #[Gedmo\SlugHandler(class: DepartmentSiteSlugHandler::class)]
    #[Groups(['department_site_read', 'department_site_read_list', 'department_site_post_write'])]
    #[ORM\Column(unique: true)]
    private ?string $slug = null;

    public function __construct(?UuidInterface $uuid = null)
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
