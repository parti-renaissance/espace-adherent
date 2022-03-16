<?php

namespace App\Firebase\DynamicLinks;

interface DynamicLinkObjectInterface
{
    public function getDynamicLinkPath(): string;

    public function withSocialMeta(): bool;

    public function getSocialTitle(): string;

    public function getSocialDescription(): string;

    public function setDynamicLink(string $link): void;

    public function getDynamicLink(): ?string;
}
