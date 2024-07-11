<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(attributes: ['order' => ['position' => 'ASC'], 'security' => "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'dashboard')", 'normalization_context' => ['groups' => ['hub_items_list']], 'pagination_items_per_page' => 30], itemOperations: [], collectionOperations: ['get' => ['path' => '/v3/hub-items']])]
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

    #[ORM\Column]
    #[Groups(['hub_items_list'])]
    #[Assert\NotBlank]
    public ?string $title = null;

    #[ORM\Column]
    #[Groups(['hub_items_list'])]
    #[Assert\NotBlank]
    #[Assert\Url]
    public ?string $url = null;

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
