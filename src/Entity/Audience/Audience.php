<?php

namespace App\Entity\Audience;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\ManagedZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Audience\AudienceRepository")
 *
 * @ApiResource(
 *     attributes={
 *         "access_control": "is_granted('ROLE_AUDIENCE')",
 *         "normalization_context": {"groups": {"audience_read"}},
 *         "denormalization_context": {"groups": {"audience_write"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/audiences",
 *             "controller": "App\Controller\Api\Audience\RetrieveAudiencesController",
 *             "access_control": "is_granted('ROLE_AUDIENCE') and is_granted('REQUEST_SCOPE_GRANTED')",
 *             "normalization_context": {
 *                 "groups": {"audience_list_read"}
 *             },
 *         },
 *         "post": {
 *             "path": "/v3/audiences",
 *             "access_control": "is_granted('ROLE_AUDIENCE') and is_granted('REQUEST_SCOPE_GRANTED')",
 *             "defaults": {"scope_position": "request"},
 *             "validation_groups": {"Default", "api_scope_context"},
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_AUDIENCE') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)",
 *         },
 *         "put": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_AUDIENCE') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)",
 *             "validation_groups": {"Default", "api_scope_context"},
 *         },
 *         "delete": {
 *             "path": "/v3/audiences/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_AUDIENCE') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)",
 *         },
 *     }
 * )
 *
 * @ManagedZone(path="zone", message="common.zone.not_managed_zone")
 */
class Audience extends AbstractAudience
{
    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     *
     * @Groups({"audience_read", "audience_write", "audience_list_read"})
     */
    private $name;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @Assert\IsTrue(groups={"api_scope_context"}, message="audience.zones.empty")
     */
    public function isValidZones(): bool
    {
        return !$this->zones->isEmpty();
    }
}
