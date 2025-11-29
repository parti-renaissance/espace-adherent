<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

trait PublicIdTrait
{
    #[ORM\Column(length: 7, unique: true, nullable: true)]
    protected ?string $publicId = null;

    #[Groups(['jemarche_user_profile', 'profile_read', 'adherent_autocomplete', 'referral_read_with_referrer', 'committee:list', 'committee:read', 'agora_read', 'agora_membership_read', 'hit:read'])]
    #[SerializedName('id')]
    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    public function setPublicId(string $publicId): void
    {
        $this->publicId = $publicId;
    }
}
