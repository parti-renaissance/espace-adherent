<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *          "order": {"position": "ASC"},
 *          "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'dashboard')",
 *          "normalization_context": {
 *              "groups": {"hub_items_list"},
 *          },
 *          "pagination_items_per_page": 30,
 *      },
 *     itemOperations={},
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/hub-items",
 *         },
 *     },
 * )
 */
#[ORM\Entity]
class HubItem implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use PositionTrait;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column]
    #[Groups(['hub_items_list'])]
    public ?string $title = null;

    /**
     * @Assert\NotBlank
     * @Assert\Url
     */
    #[ORM\Column]
    #[Groups(['hub_items_list'])]
    public ?string $url = null;

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
