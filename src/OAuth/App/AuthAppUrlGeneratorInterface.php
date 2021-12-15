<?php

namespace App\OAuth\App;

use App\Entity\Adherent;
use App\Entity\AdherentExpirableTokenInterface;
use Symfony\Component\HttpFoundation\Request;

interface AuthAppUrlGeneratorInterface
{
    public static function getAppCode(): string;

    public function guessAppCodeFromRequest(Request $request): ?string;

    public function generateHomepageLink(): string;

    public function generateCreatePasswordLink(Adherent $adherent, AdherentExpirableTokenInterface $token): string;

    public function generateLoginLink(): string;
}
