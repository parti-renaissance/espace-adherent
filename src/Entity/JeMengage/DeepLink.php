<?php

namespace App\Entity\JeMengage;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\EntityListener\DynamicLinkListener;
use App\Firebase\DynamicLinks\DynamicLinkObjectInterface;
use App\Firebase\DynamicLinks\DynamicLinkObjectTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'jemengage_deep_link')]
#[ORM\Entity]
#[ORM\EntityListeners([DynamicLinkListener::class])]
class DeepLink implements EntityAdministratorBlameableInterface, DynamicLinkObjectInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use DynamicLinkObjectTrait;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column]
    public ?string $label = null;

    /**
     * @Assert\NotBlank
     * @Assert\Url(protocols={"https"}, message="Protocole autorisé : https")
     * @Assert\Regex("#^https://.*\.?parti-renaissance\.fr/.+$#", message="Le domaine n'est pas autorisé ou le chemin n'est pas rempli")
     */
    #[ORM\Column]
    public ?string $link = null;

    #[ORM\Column(nullable: true)]
    public ?string $socialTitle = null;

    #[ORM\Column(nullable: true)]
    public ?string $socialDescription = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->label;
    }

    public function getDynamicLinkPath(): string
    {
        return $this->link;
    }

    public function withSocialMeta(): bool
    {
        return $this->socialTitle || $this->socialDescription;
    }

    public function getSocialTitle(): string
    {
        return (string) $this->socialTitle;
    }

    public function getSocialDescription(): string
    {
        return (string) $this->socialDescription;
    }
}
