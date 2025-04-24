<?php

namespace App\Entity\Audience;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Api\Audience\RetrieveAudiencesController;
use App\Repository\Audience\AudienceRepository;
use App\Validator\ManagedZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/audiences/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('ROLE_AUDIENCE') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)"
        ),
        new Put(
            uriTemplate: '/v3/audiences/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('ROLE_AUDIENCE') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)",
            validationContext: ['groups' => ['Default', 'api_scope_context']]
        ),
        new Delete(
            uriTemplate: '/v3/audiences/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('ROLE_AUDIENCE') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)"
        ),
        new GetCollection(
            uriTemplate: '/v3/audiences',
            controller: RetrieveAudiencesController::class,
            normalizationContext: ['groups' => ['audience_list_read']],
            security: "is_granted('REQUEST_SCOPE_GRANTED') and is_granted('ROLE_AUDIENCE')"
        ),
        new Post(
            uriTemplate: '/v3/audiences',
            defaults: ['scope_position' => 'request'],
            security: "is_granted('REQUEST_SCOPE_GRANTED') and is_granted('ROLE_AUDIENCE')",
            validationContext: ['groups' => ['Default', 'api_scope_context']]
        ),
    ],
    normalizationContext: ['groups' => ['audience_read']],
    denormalizationContext: ['groups' => ['audience_write']],
    security: "is_granted('ROLE_AUDIENCE')"
)]
#[ManagedZone(path: 'zone', message: 'common.zone.not_managed_zone')]
#[ORM\Entity(repositoryClass: AudienceRepository::class)]
class Audience extends AbstractAudience
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Groups(['audience_read', 'audience_write', 'audience_list_read'])]
    #[ORM\Column]
    private $name;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    #[Assert\IsTrue(groups: ['api_scope_context'], message: 'audience.zones.empty')]
    public function isValidZones(): bool
    {
        return !$this->zones->isEmpty();
    }
}
