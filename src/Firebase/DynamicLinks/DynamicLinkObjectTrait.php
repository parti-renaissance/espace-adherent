<?php

namespace App\Firebase\DynamicLinks;

use Doctrine\ORM\Mapping as ORM;

trait DynamicLinkObjectTrait
{
    #[ORM\Column(nullable: true)]
    private ?string $dynamicLink = null;

    public function getDynamicLink(): ?string
    {
        return $this->dynamicLink;
    }

    public function setDynamicLink(string $link): void
    {
        $this->dynamicLink = $link;
    }

    public function getDynamicLinkPath(): string
    {
        return '';
    }

    public function withSocialMeta(): bool
    {
        return false;
    }

    public function getSocialTitle(): string
    {
        return '';
    }

    public function getSocialDescription(): string
    {
        return '';
    }
}
