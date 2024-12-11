<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/v3/hub-items'),
    ],
    normalizationContext: ['groups' => ['hub_items_list']],
    order: ['position' => 'ASC'],
    paginationItemsPerPage: 30,
    security: 'is_granted(\'ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN\') and is_granted(\'IS_FEATURE_GRANTED\', \'dashboard\')'
)]
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

    #[Assert\NotBlank]
    #[Groups(['hub_items_list'])]
    #[ORM\Column]
    public ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Url]
    #[Groups(['hub_items_list'])]
    #[ORM\Column]
    public ?string $url = null;

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
