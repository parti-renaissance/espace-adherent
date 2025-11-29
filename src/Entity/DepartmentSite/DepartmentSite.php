<?php

declare(strict_types=1);

namespace App\Entity\DepartmentSite;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
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
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/department_sites/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%']
        ),
        new Put(
            uriTemplate: '/v3/department_sites/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['department_site_post_write']]
        ),
        new GetCollection(
            uriTemplate: '/v3/department_sites',
            normalizationContext: ['groups' => ['department_site_read_list']]
        ),
        new Post(
            uriTemplate: '/v3/department_sites',
            normalizationContext: ['groups' => ['department_site_post_write']]
        ),
    ],
    normalizationContext: ['groups' => ['department_site_read']],
    denormalizationContext: ['groups' => ['department_site_write']],
    filters: [DepartmentSiteScopeFilter::class],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'department_site')"
)]
#[ORM\Entity(repositoryClass: DepartmentSiteRepository::class)]
#[UniqueEntity(fields: ['zone'], message: 'department_site.zone.not_unique')]
#[UniqueEntity(fields: ['slug'])]
class DepartmentSite
{
    use EntityIdentityTrait;
    use UnlayerJsonContentTrait;
    use EntityTimestampableTrait;

    #[AssertZoneInScopeZones]
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
