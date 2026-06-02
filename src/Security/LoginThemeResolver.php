<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;

class LoginThemeResolver
{
    public const THEME_ATTAL = 'attal';
    public const THEME_RENAISSANCE = 'renaissance';

    public function __construct(private readonly string $userCampaignHost)
    {
    }

    public function resolve(Request $request): string
    {
        $host = $request->attributes->get('app_domain', $request->getHost());

        return $host === $this->userCampaignHost ? self::THEME_ATTAL : self::THEME_RENAISSANCE;
    }
}
